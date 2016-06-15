<?php
namespace Daftswag\Helpers;

class Config
{
    private $file;

    public function __construct($namespace = 'misc')
    {
        $this->file = __DIR__ . "/../../config/{$namespace}.cfg";
        if (!is_file($this->file)) {
            $this->save([]);
        }
    }

    public function get($key = '*')
    {
        $cfg = $this->load();
        return $key === '*' ? $cfg : (array_key_exists($key, $cfg) ? $cfg[$key] : null);
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

    private function save(array $cfg)
    {
        return file_put_contents($this->file, json_encode($cfg));
    }
}
