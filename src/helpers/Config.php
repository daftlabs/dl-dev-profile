<?php
namespace Daftswag\Helpers;

class Config
{
    const AWS_ID = 'aws_access_key_id';
    const AWS_KEY = 'aws_secret_access_key';

    private $namespace;
    private $file;
    private $awsFile;
    private $awsConfigKeys = [self::AWS_ID, self::AWS_KEY];

    public function __construct($namespace = 'default')
    {
        $this->namespace = $namespace;
        $this->file = __DIR__ . "/../../config/{$this->namespace}.config";
        if (!is_file($this->file)) {
            $this->save([]);
        }
        $this->awsFile = trim(shell_exec('echo ~/.aws/credentials'));
        if (!is_file($this->awsFile)) {
            $this->saveAws([]);
        }
    }

    public function get($key = '*')
    {
        $config = in_array($key, $this->awsConfigKeys) ? $this->loadAws($this->namespace) : $this->load();
        return $key === '*' ? $config : (array_key_exists($key, $config) ? $config[$key] : null);
    }

    public function set($key, $value)
    {
        if (!in_array($key, $this->awsConfigKeys)) {
            $this->save(array_merge($this->load(), [$key => $value]));
        } else {
            $config = $this->loadAws($this->namespace);
            $config[$key] = $value;
            $this->saveAws(array_merge($this->loadAws(), [$this->namespace => $config]));
        }
        return $this;
    }

    private function load()
    {
        return json_decode(file_get_contents($this->file), true) ?: [];
    }

    private function save(array $config)
    {
        return file_put_contents($this->file, json_encode($config));
    }

    private function loadAws($profile = null)
    {
        $config = [];
        $section = null;
        $key = null;
        $value = null;
        foreach (explode("\n", file_get_contents($this->awsFile)) as $line) {
            if (substr($line, 0, 1) === '[') {
                $section = trim(str_replace(['[', ']'], '', $line));
            } elseif (stristr($line, '=')) {
                $parts = explode('=', $line);
                $key = trim($parts[0]);
                $value = trim($parts[1]);
            }

            if ($section && $key && $value) {
                if (!array_key_exists($section, $config)) {
                    $config[$section] = [];
                }
                $config[$section][$key] = $value;
            }
        }

        if ($profile) {
            return array_key_exists($profile, $config) ? $config[$profile] : [
                static::AWS_ID => null,
                static::AWS_KEY => null,
            ];
        }
        return $config;
    }

    private function saveAws(array $config)
    {
        $content = [];
        foreach ($config as $profile => $vars) {
            $content[] = "[{$profile}]";
            foreach ($vars as $key => $value) {
                $content[] = "{$key} = {$value}";
            }
        }
        return file_put_contents($this->awsFile, implode("\n", $content));
    }
}
