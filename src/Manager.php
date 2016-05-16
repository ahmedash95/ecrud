<?php

namespace Ahmedash95\Ecrud;

use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Collection;

class Manager
{
    /**
     * The Filesystem instance.
     *
     * @var Filesystem
     */
    private $disk;

    /**
     * The Package config file.
     *
     * @var
     */
    private $config;

    /**
     * The generated views path.
     *
     * @var
     */
    private $viewsPath;

    /**
     * Manager constructor.
     *
     * @param Filesystem $disk
     * @param array      $config
     */
    public function __construct(Filesystem $disk, array $config, $viewsPaths)
    {
        $this->disk = $disk;
        $this->config = $config;
        $this->viewsPaths = $viewsPaths;
    }

    public function setViewsPath($path)
    {
        $view_path = $this->viewsPaths[0];
        $lastDirectory = $view_path;
        foreach (explode('/', $path) as $directory) {
            $lastDirectory .= '/'.$directory;
            if (!is_dir($lastDirectory)) {
                mkdir($lastDirectory);
            }
        }

        if (!is_dir($view_path.'/'.$path)) {
            mkdir($view_path.'/'.$path);
        }
        $this->viewsPath = $view_path.'/'.$path;
    }

    public function getStubsPath()
    {
        return __DIR__.'/../templates/'.$this->config['framework'];
    }

    public function getStubByType($type)
    {
        $path = $this->getStubsPath();
        return $path.'/'.$type.'.stub';
    }

    public function createFileWithFields(array $migration, $allowOverride = false)
    {
        $fileContent = '';

        if ($this->config['extends'] != null) {
            $fileContent .= "@extends('{$this->config['extends']}')\n";
        }
        if ($this->config['section'] != null) {
            $fileContent .= "@section('{$this->config['section']}')\n";
        }
        $fileContent .= "<form action=\"\" method=\"post\">\n<input type=\"hidden\" name=\"_token\" value=\"{{ csrf_token }}\">";
        foreach ($migration['fields'] as $field) {
            $stub = file_get_contents($field['stub']);
            // replace label name
            $label = ucfirst(str_replace('_', ' ', $field['name']));
            $stub = str_replace('$label$', $label, $stub);
            // replace input name
            $stub = str_replace('$name$', $field['name'], $stub);
            $fileContent .= $stub;
        }
        if ($this->config['section'] != null) {
            $fileContent .= "<button type=\"submit\">Submit</button>\n</form>\n@endsection\n";
        }

        if (file_exists($this->viewsPath.'/create.blade.php') && !$allowOverride) {
            throw new \Exception('View file already exists!');
        }
        file_put_contents($this->viewsPath.'/create.blade.php', $fileContent);
        
        $fileContent = $this->formWithValues($fileContent);
        file_put_contents($this->viewsPath.'/edit.blade.php', $fileContent);

        $fileContent = $this->createIndexFile($migration);
        file_put_contents($this->viewsPath.'/index.blade.php', $fileContent);
    }

    public function formWithValues($content)
    {
        preg_match_all('#\<(?:input|select).*name=\"(.*?)\"#', $content, $matches);
        foreach ($matches[0] as $key => $match) {
            $inputWithValue = $match.' value="{{ $row->'.$matches[1][$key].' }}"';
            $content = str_replace($match, $inputWithValue, $content);
        }
        preg_match_all('#\<(?:textarea).*name=\"(.*?)\".*><#', $content, $matches);
        foreach ($matches[0] as $key => $match) {
            $match = rtrim($match,'<');
            $inputWithValue = $match.'{{ $row->'.$matches[1][$key].' }}';
            $content = str_replace($match, $inputWithValue, $content);
        }

        return $content;
    }
    /**
    * This method try to filter fileds when using option ( except || only )  in command
    */
    public function filterMatches(array $matches, array $except, array $only)
    {
        if (!empty($except[0]) && empty($only[0])) {
            foreach ($matches[2] as $key => $match) {
                if (in_array($match, $except)) {
                    unset($matches[1][$key], $matches[2][$key]);
                }
            }
        } elseif (!empty($only[0]) && empty($except[0])) {
            foreach ($matches[2] as $key => $match) {
                if (!in_array($match, $only)) {
                    unset($matches[1][$key], $matches[2][$key]);
                }
            }
        }

        return $matches;
    }

    public function createIndexFile($migration)
    {
        $content = '';
        if ($this->config['extends'] != null) {
            $content .= "@extends('{$this->config['extends']}')\n";
        }
        if ($this->config['section'] != null) {
            $content .= "@section('{$this->config['section']}')\n";
        }
        $content .= file_get_contents($this->getStubByType('index'));
        $content = str_replace('$title$', ucfirst($migration['name']), $content);
        $thead = '';
        $tbody = '';
        foreach ($migration['fields'] as $field) {
            $thead .= '     <td>'.$field['name']."<td>\n";
            $tbody .= "     <td></td>\n";
        }
        $thead = rtrim($thead, "\n");
        $tbody = rtrim($tbody, "\n");
        $content = str_replace('$table_head$', $thead, $content);
        $content = str_replace('$table_body$', $tbody, $content);
        if ($this->config['section'] != null) {
            $content .= "\n@endsection\n";
        }

        return $content;
    }
}
