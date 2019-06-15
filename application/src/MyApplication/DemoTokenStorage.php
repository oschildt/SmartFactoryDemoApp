<?php

namespace MyApplication;

use OAuth2\Interfaces\ITokenStorage;

class DemoTokenStorage implements ITokenStorage
{
    protected $supported_keys = ['user_id', 'client_id', 'access_token', 'refresh_token'];
    protected $records = [];
    protected $storage_file = null;
    
    protected function loadRecords()
    {
        $this->validateParameters();
        
        if (!file_exists($this->storage_file)) {
            return true;
        }
        
        $xmldoc = new \DOMDocument();
        
        if (!@$xmldoc->load($this->storage_file)) {
            throw new \Exception(sprintf("The 'storage_file' is invalid!"));
        }
        
        \SmartFactory\dom_to_array($xmldoc->documentElement, $this->records);
        
        return true;
    }
    
    protected function saveRecords()
    {
        $this->validateParameters();
        
        $xmldoc = new \DOMDocument("1.0", "UTF-8");
        
        $xmldoc->formatOutput = true;
        
        $root = $xmldoc->createElement("array");
        $root = $xmldoc->appendChild($root);
        
        if (!empty($this->records)) {
            \SmartFactory\array_to_dom($root, $this->records);
        }
        
        if (!@$xmldoc->save($this->storage_file)) {
            throw new \Exception(sprintf("The storage file '%s' cannot be written!", $this->storage_file));
        }
        
        return true;
    }
    
    protected function validateParameters()
    {
        if (empty($this->storage_file)) {
            throw new \Exception("The 'storage_file' is not specified!");
        }
        
        if (!is_writable(dirname($this->storage_file)) || (file_exists($this->storage_file) && !is_writable($this->storage_file))) {
            throw new \Exception(sprintf("The storage file '%s' is not writable!", $this->storage_file));
        }
        
        return true;
    }
    
    public function init($params)
    {
        if (!empty($params["storage_file"])) {
            $this->storage_file = $params["storage_file"];
        }
        
        $this->validateParameters();
    }
    
    public function saveTokenRecord(&$token_record)
    {
        if (empty($token_record["user_id"])) {
            throw new \Exception("The parameter 'user_id' is not specified!");
        }
        
        if (empty($token_record["client_id"])) {
            throw new \Exception("The parameter 'client_id' is not specified!");
        }
        
        if (empty($token_record["access_token"])) {
            throw new \Exception("The parameter 'access_token' is not specified!");
        }
        
        if (empty($token_record["refresh_token"])) {
            throw new \Exception("The parameter 'refresh_token' is not specified!");
        }
        
        if (empty($token_record["access_token_expire"])) {
            throw new \Exception("The parameter 'access_token_expire' is not specified!");
        }
        
        if (!is_numeric($token_record["access_token_expire"]) || $token_record["access_token_expire"] < 0) {
            throw new \Exception("The parameter 'access_token_expire' is not valid!");
        }
        
        if (empty($token_record["refresh_token_expire"])) {
            throw new \Exception("The parameter 'refresh_token_expire' is not specified!");
        }
        
        if (!is_numeric($token_record["refresh_token_expire"]) || $token_record["refresh_token_expire"] < 0) {
            throw new \Exception("The parameter 'refresh_token_expire' is not valid!");
        }
        
        $this->loadRecords();
        
        $new_record = [
            "user_id" => $token_record["user_id"],
            "client_id" => $token_record["client_id"],
            "access_token" => $token_record["access_token"],
            "refresh_token" => $token_record["refresh_token"],
            "access_token_expire" => $token_record["access_token_expire"],
            "refresh_token_expire" => $token_record["refresh_token_expire"]
        ];
        
        $exists = false;
        foreach ($this->records as &$record) {
            if ($record["user_id"] == $new_record["user_id"] && $record["client_id"] == $new_record["client_id"]) {
                $record["access_token"] = $new_record["access_token"];
                $record["refresh_token"] = $new_record["refresh_token"];
                $record["access_token_expire"] = $new_record["access_token_expire"];
                $record["refresh_token_expire"] = $new_record["refresh_token_expire"];
                
                $exists = true;
            }
        }
        
        if (!$exists) {
            $this->records[] = $new_record;
        }
        
        $this->saveRecords();
        
        return true;
    }
    
    public function deleteTokenRecordByKey($key, $value)
    {
        if (!in_array($key, $this->supported_keys)) {
            throw new \Exception(sprintf("The key %s is not supported! The suppoted keys are: %s", $key, implode(", ", $this->supported_keys)));
        }
        
        $this->loadRecords();
        
        $exists = false;
        
        foreach ($this->records as $i => $record) {
            if ($record[$key] == $value) {
                unset($this->records[$i]);
                
                $exists = true;
            }
        }
        
        $this->records = array_values($this->records);
        
        if (!$exists) {
            throw new \Exception("No records found with $key=$value!");
        }
        
        $this->saveRecords();
        
        return true;
    }
    
    public function verifyAccessToken($access_token, $user_id, $client_id)
    {
        $this->loadRecords();
        
        $exists = false;
        
        foreach ($this->records as $record) {
            if ($record["access_token"] == $access_token && $record["user_id"] == $user_id && $record["client_id"] == $client_id) {
                
                if (time() > $record["access_token_expire"]) {
                    throw new \OAuth2\InvalidTokenException("The access token is expired!");
                }
                
                $exists = true;
            }
        }
        
        if (!$exists) {
            throw new \OAuth2\InvalidTokenException("The access token is invalid!");
        }
        
        return true;
    }
    
    public function verifyRefreshToken($refresh_token, $user_id, $client_id)
    {
        $this->loadRecords();
        
        $exists = false;
        
        foreach ($this->records as $record) {
            if ($record["refresh_token"] == $refresh_token && $record["user_id"] == $user_id && $record["client_id"] == $client_id) {
                
                if (time() > $record["refresh_token_expire"]) {
                    throw new \OAuth2\InvalidTokenException("The refresh token is expired!");
                }
                
                $exists = true;
            }
        }
        
        if (!$exists) {
            throw new \OAuth2\InvalidTokenException("The refresh token is invalid!");
        }
        
        return true;
    }
}