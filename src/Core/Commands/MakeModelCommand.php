<?php
namespace LARAVEL\Core\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class MakeModelCommand extends Command
{
    protected function configure()
    {
        $this
            ->setName('make:model')
            ->setDescription('Creates a new model')
            ->addArgument('name', InputArgument::REQUIRED, 'Tên của Model');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        
        $parts = explode('/', $name);
        $modelName = array_pop($parts);
        $directory = (!empty(implode('/', $parts)))?'\\'.implode('\\', $parts):'';
        $modelsDir = __DIR__ . "/../../Models";
        $modelDir = $modelsDir . '/' . $directory;
        $filePath = $modelDir . "/{$modelName}.php";
        if (!is_dir($modelDir)) {
            mkdir($modelDir, 0755, true);
        }
        if (!file_exists($filePath)) {
            $modelTemplate = "<?php\n\nnamespace LARAVEL\\Models$directory;\n\nuse LARAVEL\DatabaseCore\Eloquent\Model;\nuse LARAVEL\DatabaseCore\Eloquent\Factories\HasFactory;\n\nclass $modelName extends Model\n{\n    use HasFactory;\n\n    // Your model code here\n}\n";
            file_put_contents($filePath, $modelTemplate);
            $output->writeln("<info>Model $modelName đã được tạo thành công.</info>");
        } else {
            $output->writeln("<comment>Model $modelName đã tồn tại.</comment>");
        }

        return Command::SUCCESS;
    }
}