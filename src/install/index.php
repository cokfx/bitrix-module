<?php

class bitrix_module extends CModule
{
    public $MODULE_ID = "bitrix_module";
    public $MODULE_NAME = "ArtEast. Модуль";
	public $MODULE_VERSION = '1.0';
  	public $MODULE_VERSION_DATE = '2018-12-12';

    public function __construct()
    {
        $this->PARTNER_NAME = "ArtEast";
        $this->PARTNER_URI = "https://arteast.ru";
    }

    public function DoInstall()
    {
        $file = __DIR__.'/do-install.php';
        if (file_exists($file)) {
            include $file;
        }

        $this->copyInstallFiles();
        $this->addUrlRewriteRules();
		$this->addSymlinkAdmin();
		$this->addSymlinkComponents();
        RegisterModule($this->MODULE_ID);
    }

    public function DoUninstall()
    {
        $file = __DIR__.'/do-uninstall.php';
        if (file_exists($file)) {
            include $file;
        }

		$this->removeSymlinkAdmin();
		$this->removeSymlinkComponents();
        UnRegisterModule($this->MODULE_ID);
    }

	public function addSymlinkAdmin()
	{
		$dir = __DIR__.'/../admin';
		if (!file_exists($dir)) {
			return;
		}

		$pages = scandir($dir);
		foreach ($pages as $page) {
			if (in_array($page, ['.', '..', 'menu.php'])) {
				continue;
			}
			symlink(
				$dir.'/'.$page,
				$this->getBitrixAdminPageName($page)
			);
		}
	}

	public function addSymlinkComponents()
	{
		$dir = __DIR__.'/../components';
		if (file_exists($dir)) {
			symlink($dir, $this->getBitrixComponentsDir());
		}
	}

    public function addUrlRewriteRules()
    {
        if (file_exists(__DIR__.'/urlrewrite.php')) {
            $rules = include __DIR__.'/urlrewrite.php';
            if (is_array($rules)) {
                foreach ($rules as $rule) {
                    \CUrlRewriter::add($rule);
                }
            }
        }
    }

    public function copyInstallFiles()
    {
        $dir = __DIR__.'/public';
        if (file_exists($dir) && is_dir($dir)) {
            $files = scandir($dir);
            foreach ($files as $file) {
                if (in_array($file, ['.', '..'])) {
                    continue;
                }

                $sourcePath = $dir.'/'.$file;
                $targetPath = $_SERVER['DOCUMENT_ROOT'].'/'.$file;
                if (file_exists($targetPath)) {
                    if (is_dir($targetPath)) {
                        // pass
                    }
                    else {
                        continue;
                    }
                }
                else if (!file_exists($targetPath)) {
                    mkdir($targetPath, 0755, true);
                }

                CopyDirFiles($sourcePath, $targetPath, false, true, false);
            }
        }
    }

	public function removeSymlinkAdmin()
	{
		$dir = __DIR__.'/../admin';
		if (!file_exists($dir)) {
			return;
		}

		$pages = scandir($dir);
		foreach ($pages as $page) {
			if (in_array($page, ['.', '..', 'menu.php'])) {
				continue;
			}
			unlink($this->getBitrixAdminPageName($page));
		}
	}

	public function removeSymlinkComponents()
	{
		$dir = $this->getBitrixComponentsDir();
		if (file_exists($dir)) {
			unlink($dir);
		}
	}

	protected function getBitrixComponentsDir()
	{
		return $_SERVER['DOCUMENT_ROOT'].'/bitrix/components/'.$this->MODULE_ID;
	}

	protected function getBitrixAdminPageName(string $page)
	{
		return $_SERVER['DOCUMENT_ROOT'].'/bitrix/admin/'.$this->MODULE_ID.'_'.$page;
	}
}
