<?php

namespace Ahmedash95\Ecrud\Commands;

use Ahmedash95\Ecrud\Manager;
use Illuminate\Console\Command;

class CreateFromMigrationCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ecrud:migration {name} {--force} {--except=} {--only=} {--path=}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a crud views from migration.';

    /**
     * The Ecrud manager instance.
     *
     * @var \Ahmedash95\Ecrud\Manager
     */
    private $manager;

    private $migrationFile;

    private $allowedTypes = ['string', 'text', 'bigIncrements', 'bigInteger', 'binary', 'boolean', 'char', 'date', 'dateTime', 'decimal', 'double', 'enum', 'float', 'integer', 'ipAddress', 'json', 'jsonb', 'longText', 'macAddress', 'mediumInteger', 'mediumText', 'morphs', 'smallInteger', 'time', 'tinyInteger'];

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(Manager $manager)
    {
        parent::__construct();

        $this->manager = $manager;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $fileName = str_replace('.php', '', $this->input->getArgument('name'));
        $migrationsFiles = $this->manager->allMigrations();
        try {
            $this->migrationFile = $migrationsFiles[$fileName];
        } catch (\ErrorException $e) {
            return $this->error(sprintf('Migration %s.php not found.', $fileName));
        }

        $migration['name'] = $this->getMigrationName();
        $viewsPath = $this->option('path') ?: $migration['name'];
        $this->manager->setViewsPath($viewsPath);

        $migration['fields'] = $this->collectFieldsFromMigration();

        $migration['fields'] = $this->loadStubsForFields($migration['fields']);

        $override = $this->option('force');

        $this->manager->createFileWithFields($migration, $override);

        return $this->info('Crud Created Successfully');
    }

    public function getMigrationName()
    {
        $fileContent = file_get_contents($this->migrationFile['path']);
        preg_match_all('#Schema::\w+\(\'(\w+)\'#', $fileContent, $matches);
        if (isset($matches[1][0])) {
            return $matches[1][0];
        }
        /*
            if pattern matches is null then i will try to get the name from
            filename itself
        */
        preg_match_all('#\_(?:create|append|add)\_(\w+)\_table#', $this->migrationFile['path'], $matches);
        if (isset($matches[1][0])) {
            return $matches[1][0];
        }

        throw new \Exception("Can't Recognize table name");
    }

    private function collectFieldsFromMigration()
    {
        $fileContent = file_get_contents($this->migrationFile['path']);
        preg_match_all('#\$\w+\-\>(\w+)\(\'(\w+)\'\)#', $fileContent, $matches);
        $only = explode(',', $this->option('only'));
        $except = explode(',', $this->option('except'));
        $matches = $this->manager->filterMatches($matches, $except, $only);
        $fields = [];

        foreach ($matches[2] as $key => $match) {
            if (!in_array($matches[1][$key], $this->allowedTypes)) {
                continue;
            }
            $fields[] = [
                'name' => $match,
                'type' => $matches[1][$key],
            ];
        }

        return $fields;
    }

    public function loadStubsForFields(array $fields)
    {
        foreach ($fields as $key => $field) {
            switch ($field['type']) {
                case 'string':
                default:
                    $stub = $this->manager->getStubByType('string');
                    break;
                case 'text':
                    $stub = $this->manager->getStubByType('text');
                    break;
                case 'email':
                    $stub = $this->manager->getStubByType('email');
                    break;
                case 'date':
                    $stub = $this->manager->getStubByType('date');
                    break;
                case 'datetime':
                    $stub = $this->manager->getStubByType('datetime');
                    break;
                case 'boolean':
                    $stub = $this->manager->getStubByType('boolean');
                    break;
            }
            $fields[$key]['stub'] = $stub;
        }

        return $fields;
    }
}
