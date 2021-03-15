<?php

namespace Tests\Generators;

use Tests\TestCase;

class ModelFactoryGeneratorTest extends TestCase
{
    /** @test */
    public function it_creates_correct_model_factory_content()
    {
        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $modelFactoryPath = database_path('factories/'.$this->model_name.'Factory.php');
        $this->assertFileExists($modelFactoryPath);
        $modelFactoryContent = "<?php

use App\User;
use {$this->full_model_name};
use Faker\Generator as Faker;

\$factory->define({$this->model_name}::class, function (Faker \$faker) {

    return [
        'name' => \$faker->word,
        'description' => \$faker->sentence,
        'creator_id' => function () {
            return factory(User::class)->create()->id;
        },
    ];
});
";
        $this->assertEquals($modelFactoryContent, file_get_contents($modelFactoryPath));
    }

    /** @test */
    public function it_creates_model_factory_file_content_from_published_stub()
    {
        app('files')->makeDirectory(base_path('stubs/simple-crud/database/factories'), 0777, true, true);
        app('files')->copy(
            __DIR__.'/../stubs/database/factories/model-factory.stub',
            base_path('stubs/simple-crud/database/factories/model-factory.stub')
        );
        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $modelFactoryPath = database_path('factories/'.$this->model_name.'Factory.php');
        $this->assertFileExists($modelFactoryPath);
        $modelFactoryContent = "<?php

declare(strict_types = 1);

namespace Database\Factories;

use App\Models\User;
use {$this->full_model_name};
use Illuminate\Database\Eloquent\Factories\Factory;

class {$this->model_name}Factory extends Factory
{
    protected \$model = {$this->model_name}::class;

    public function definition()
    {
        return [
            'title'       => \$this->faker->word,
            'description' => \$this->faker->sentence,
            'creator_id'  => function () {
                return User::factory()->create()->id;
            },
        ];
    }
}
";
        $this->assertEquals($modelFactoryContent, file_get_contents($modelFactoryPath));
        $this->removeFileOrDir(base_path('stubs'));
    }

    /** @test */
    public function it_doesnt_override_the_existing_model_factory_content()
    {
        // $this->artisan('make:model', ['name' => 'Models/'.$this->model_name, '--no-interaction' => true]);
        $this->artisan('make:factory', ['name' => $this->model_name.'Factory', '--no-interaction' => true]);
        $this->artisan('make:crud', ['name' => $this->model_name, '--no-interaction' => true]);

        $modelFactoryPath = database_path('factories/'.$this->model_name.'Factory.php');
        $this->assertFileExists($modelFactoryPath);
        $modelFactoryContent = "<?php

namespace Database\Factories;

use App\Model;
use Illuminate\Database\Eloquent\Factories\Factory;

class {$this->model_name}Factory extends Factory
{
    /**
     * The name of the factory's corresponding model.
     *
     * @var string
     */
    protected \$model = Model::class;

    /**
     * Define the model's default state.
     *
     * @return array
     */
    public function definition()
    {
        return [
            //
        ];
    }
}
";
        $this->assertEquals($modelFactoryContent, file_get_contents($modelFactoryPath));
    }
}
