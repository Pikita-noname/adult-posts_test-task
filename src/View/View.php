<?php

namespace App\View;

use RuntimeException;
use Smarty\Exception;
use Smarty\Smarty;

final class View
{
    private Smarty $smarty;

    public function __construct(string $root, array $config)
    {
        foreach (['compile', 'cache'] as $directory) {
            $path = $root . '/var/smarty/' . $directory;

            if (!is_dir($path)) {
                if (!mkdir($path, 0775, true) && !is_dir($path)) {
                    throw new RuntimeException('Cannot create Smarty directory: ' . $path);
                }
            }
        }

        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir($root . '/templates');
        $this->smarty->setCompileDir($root . '/var/smarty/compile');
        $this->smarty->setCacheDir($root . '/var/smarty/cache');
        $this->smarty->setEscapeHtml(true);
        $this->smarty->setCompileCheck($config['debug']);
        $this->smarty->assign('appName', $config['name']);
    }

    /**
     * @throws Exception
     */
    public function render(string $template, array $data = []): string
    {
        $view = $this->smarty->createTemplate($template);
        $view->assign($data);

        return $view->fetch();
    }
}
