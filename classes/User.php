<?php

class User {
    public $preferences;
    public $settings;
    private $token;
    private $debug = false;
    
    public function __construct() {
        $this->preferences = null;
        $this->settings = null;
        $this->token = bin2hex(random_bytes(32));
    }
    
    public function __destruct() {
        if ($this->preferences && $this->validateToken()) {
            if ($this->debug) {
                error_log("Executing preferences...");
            }
            $this->preferences->applyThemeConfig();
        }
    }
    
    private function validateToken() {
        return isset($_SESSION['user_token']) && 
               hash_equals($_SESSION['user_token'], $this->token);
    }
}

class ThemeConfigManager {
    public $assetAction;
    public $target;
    private $allowedActions = ['view', 'edit'];
    private $logFile = 'debug.log'; 
    
    public function __construct() {
        $this->assetAction = null;
        $this->target = null;
    }
    
    public function applyThemeConfig() {
        if ($this->assetAction && $this->target && 
            in_array($this->assetAction->type, $this->allowedActions)) {
            file_put_contents($this->logFile, "Executing action: " . $this->assetAction->type);
            $this->assetAction->applyAssetAction($this->target);
        }
    }
}

class ThemeAssetAction {
    public $assetManager;
    public $type;
    private $tempDir = '/tmp'; 
    
    public function __construct() {
        $this->assetManager = null;
        $this->type = 'view';
    }
    
    public function applyAssetAction($target) {
        if ($this->assetManager && $this->validateTarget($target)) {
            $this->assetManager->saveAsset();
        }
    }
    
    private function validateTarget($target) {
        return preg_match('/^[a-zA-Z0-9_-]+$/', $target);
    }
} 