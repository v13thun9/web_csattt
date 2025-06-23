<?php
class UserPreferences {
    public $theme;
    public $fontSize;
    public $assetManager;
    
    public function __construct($theme = 'light', $fontSize = 'medium') {
        $this->theme = $theme;
        $this->fontSize = $fontSize;
    }

    public function __destruct() {
        if ($this->assetManager) {
            $this->assetManager->saveAsset();
        }
    }
}
?>