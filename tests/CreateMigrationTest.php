<?php

class CreateMigrationTest extends TestCase
{
    public function testGenerateCrudFromMigration()
    {
        $this->artisan('ecrud:migration', ['name' => 'create_users_table']);
        $this->assertEquals($this->consoleOutput(), "Crud Created Successfully\n");
    }
    public function testForceGenerateCrudFromMigration()
    {
        $this->testGenerateCrudFromMigration();
        $this->artisan('ecrud:migration', ['name' => 'create_users_table', '--force' => true]);
        $this->assertEquals($this->consoleOutput(), "Crud Created Successfully\n");
    }
    public function testDefineThePathForTheGeneratedCrudFromMigration()
    {
        $this->artisan('ecrud:migration', [
            'name'    => 'create_users_table',
            '--path'  => 'panel/users',
            '--force' => true,
        ]);
        $this->assertFileExists(app('config')['view']['paths'][0] . '/panel/users/create.blade.php');
        $this->assertFileExists(app('config')['view']['paths'][0] . '/panel/users/edit.blade.php');
        $this->assertFileExists(app('config')['view']['paths'][0] . '/panel/users/index.blade.php');
    }
    public function testOnlyFieldsWithCrudMigration()
    {
        $this->artisan('ecrud:migration', [
            'name'    => 'create_users_table',
            '--only'    => 'email',
            '--force' => true,
        ]);
        $content = file_get_contents(app('config')['view']['paths'][0].'/users/create.blade.php');
        preg_match_all('#input.*name\=\"(.*)\"\s#',$content,$matches);
        $this->assertSame($matches[1],['email']);
    }
    public function testExceptFieldsWithCrudMigration()
    {
        $this->artisan('ecrud:migration', [
            'name'    => 'create_users_table',
            '--except'    => 'email',
            '--force' => true,
        ]);
        $content = file_get_contents(app('config')['view']['paths'][0].'/users/create.blade.php');
        preg_match_all('#input.*name\=\"(.*)\"\s#',$content,$matches);
        $this->assertSame($matches[1],['name','password']);
    }

}
