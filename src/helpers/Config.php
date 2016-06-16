<?php
namespace Daftswag\Helpers;

class Config
{
    const AWS_ID = 'aws_access_key_id';
    const AWS_KEY = 'aws_secret_access_key';

    private $namespace;
    private $file;

    public function __construct($namespace = 'default')
    {
        $this->namespace = $namespace;
        $this->file = __DIR__ . "/../../config/{$this->namespace}.config";
        if (!is_file($this->file)) {
            $this->save([]);
        }
    }

    public function get($key = '*')
    {
        $config = $this->load();
        return $key === '*' ? $config : (array_key_exists($key, $config) ? $config[$key] : null);
    }

    public function set($key, $value)
    {
        $this->save(array_merge($this->load(), [$key => $value]));
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
}
