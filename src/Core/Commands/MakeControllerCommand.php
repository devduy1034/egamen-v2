<?php
namespace LARAVEL\Core\Commands;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeControllerCommand extends Command
{
    //Tạo command controller
    protected function configure()
    {
        $this->setName('make:controller')
            ->setDescription('Creates a new controller')
            ->addArgument('name', InputArgument::REQUIRED, 'Tên của class Controller');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $name = $input->getArgument('name');
        $parts = explode('/', $name);
        $controllerName = array_pop($parts);
        $directory = (!empty(implode('/', $parts))) ? '\\' . implode('\\', $parts) : '';
        $controllersDir = __DIR__ . "/../../Controllers";
        $controllerDir = $controllersDir . '/' . $directory;
        $filePath = $controllerDir . "/{$controllerName}.php";
        if (!is_dir($controllerDir)) {
            mkdir($controllerDir, 0755, true);
        }
        if (!file_exists($filePath)) {
            $controllerTemplate = "<?php\n\nnamespace LARAVEL\\Controllers$directory;\n\nclass $controllerName{\n\n    // Your controller code here\n}\n";
            file_put_contents($filePath, $controllerTemplate);
            $output->writeln("<info>Controller $controllerName đã được tạo thành công.</info>");
        } else {
            $output->writeln("<comment>Controller $controllerName đã tồn tại tại.</comment>");
        }
        return Command::SUCCESS;
    }
}