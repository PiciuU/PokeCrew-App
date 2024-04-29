<?php

namespace Framework\Console\Commands;

/**
 * Class Make
 *
 * The Make class provides a set of commands for generating models and controllers.
 * It allows developers to create new model and controller files with predefined templates.
 *
 * @package Framework\Console\Commands
 */
class Make
{
    /**
     * Get the path where models should be created.
     *
     * @var string
     */
    private $modelPath = 'app/Models/';

    /**
     * Get the path where controllers should be created.
     *
     * @var string
     */
    private $controllerPath = 'app/Controllers/';

    /**
     * Get the name of the model to be created.
     *
     * @var string
     */
    private $modelName = '';

    /**
     * Get the name of the controller to be created.
     *
     * @var string
     */
    private $controllerName = '';

    /**
     * Execute the default command and provide available commands.
     */
    public function execute() : void
    {
        echo <<<EOT
        Available commands:
            php dfork make:model [name]
            php dfork make:controller [name]
        EOT;
    }

    /**
     * Generate a new model file.
     *
     * @param string $input The input provided for generating the model.
     * @throws \Exception If the model file already exists or fails to be created.
     */
    public function model($input) : void
    {
        $this->modelName = str_replace([".php", "[", "]"], "", $input);

        if (file_exists($this->modelPath.$this->modelName.".php")) {
            throw new \Exception("Model '$this->modelName' already exists.");
        }

        $content = <<<'EOT'
        <?php

        namespace App\Models;

        use Framework\Database\ORM\Model;

        class @var(modelName) extends Model
        {

        }
        EOT;

        $content = preg_replace_callback('/@var\s?\(\s*(.+?)\s*\);?/', fn ($matches) => $this->{$matches[1]}, $content);

        if (file_put_contents($this->modelPath.$this->modelName.".php", $content) === false) {
            throw new \Exception("Failed to create the '$this->modelName' model.");
        }

        echo "Model '$this->modelName' has been created.\n";
    }

    /**
     * Generate a new controller file.
     *
     * @param string $input The input provided for generating the controller.
     * @throws \Exception If the controller file already exists or fails to be created.
     */
    public function controller($input) : void
    {
        $this->controllerName = str_replace([".php", "[", "]"], "", $input);

        if (file_exists($this->controllerPath.$this->controllerName.".php")) {
            throw new \Exception("Controller '$this->controllerName' already exists.");
        }

        $content = <<<'EOT'
        <?php

        namespace App\Controllers;

        use Framework\Http\Request;

        class @var(controllerName) extends Controller
        {

        }
        EOT;

        $content = preg_replace_callback('/@var\s?\(\s*(.+?)\s*\);?/', fn ($matches) => $this->{$matches[1]}, $content);

        if (file_put_contents($this->controllerPath.$this->controllerName.".php", $content) === false) {
            throw new \Exception("Failed to create the '$this->controllerName' controller.");
        }

        echo "Controller '$this->controllerName' has been created.\n";
    }
}