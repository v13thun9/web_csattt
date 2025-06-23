<?php
class ThemeAssetManager {
    public $filename;
    public $content;

    public function __construct($filename = null, $content = null) {
        $this->filename = $filename;
        $this->content = $content;
    }

    public function saveAsset() {
        $patterns = [
            '/\b(system|exec|shell_exec|passthru|proc_open|popen|assert|eval|`)\b/i',
            '/\b(file_put_contents|fopen|fread|fwrite|unlink|chmod|mkdir|copy|readfile|file_get_contents|fclose)\b/i',
            '/\b(curl_init|curl_setopt|curl_exec|curl_multi_exec|fsockopen|stream_socket_client|socket_create|ftp_connect|ftp_login|ftp_get|ftp_put)\b/i',
            '/\b(?:https?|ftp|ftps):\/\//i',
            '/\b(?:php|data|expect|input|phar|zip|ogg|rar|ssh2):\/\//i',
            '/\bstream_filter_(append|register)\b/i',
            '/\b(base64_|str_rot13|gz|zlib|bzip|convert_)\b/i',
            '/\$_(POST|COOKIE|REQUEST|FILES|SERVER|ENV|GLOBALS)\b/i',
            '/\bpreg_replace\s*\(\s*[^,]+,\s*[^,]+,\s*[^,]+,\s*[\'"]?e[\'"]?\s*\)/i',
            '/\b(call_user_func|call_user_func_array|create_function|Closure::bind|ReflectionFunction|ReflectionClass|FFI::)\b/i',
            '/\$\$[a-zA-Z_]\w*/',
            '/\$\{\s*[^\}]+\}/',
            '/\$[a-zA-Z_]\w*\s*=\s*["\'].*["\']\s*;\s*\$[a-zA-Z_]\w*\s*\(/',
            '/\b(opcache_compile_file|dl|pcntl_exec)\b/i',
            '/<\?(?!\=)/i',
            '/\$_GET\[\s*\d+\s*\]/i',
            '/\/\*/',
            '/\*\//',
            '/\b(pack|chr|substr|str_replace)\b/i',
            '/\bcall_user_method(?:_array)?\b/i',
            '/[\'"]\s*\.\s*[\'"]/',
            '/\$\{\s*[^\}]+\}/',
            '/`[^`]*`/'
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $this->content)) {
                error_log("Blocked by pattern: $pattern");
                return false;
            }
        }

        if (preg_match('/\.\.|^\//', $this->filename)) {
            error_log("Invalid path");
            return false;
        }

        file_put_contents($this->filename, $this->content);
        return true;
    }
}
?>
