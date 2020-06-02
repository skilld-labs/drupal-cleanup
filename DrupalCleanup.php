<?php

namespace SkilldDrupal;

use Composer\Composer;
use Composer\EventDispatcher\EventSubscriberInterface;
use Composer\Installer\PackageEvent;
use Composer\Installer\PackageEvents;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Plugin\PluginInterface;
use Composer\Util\Filesystem;

/**
 * A Composer plugin to remove files in Drupal packages.
 */
class DrupalCleanup implements PluginInterface, EventSubscriberInterface {

  /**
   * Composer object.
   *
   * @var \Composer\Composer
   */
  protected $composer;

  /**
   * IO object.
   *
   * @var \Composer\IO\IOInterface
   */
  protected $io;

  /**
   * {@inheritdoc}
   */
  public function activate(Composer $composer, IOInterface $io) {
    $this->composer = $composer;
    $this->io = $io;
  }

  /**
   * {@inheritdoc}
   */
  public function deactivate(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public function uninstall(Composer $composer, IOInterface $io) {
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      PackageEvents::POST_PACKAGE_INSTALL => 'onPostPackageInstall',
      PackageEvents::POST_PACKAGE_UPDATE  => 'onPostPackageUpdate',
    ];
  }

  /**
   * POST_PACKAGE_INSTALL event handler.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPostPackageInstall(PackageEvent $event) {
    $this->cleanPackage($event->getOperation()->getPackage());
  }

  /**
   * POST_PACKAGE_UPDATE event handler.
   *
   * @param \Composer\Installer\PackageEvent $event
   */
  public function onPostPackageUpdate(PackageEvent $event) {
    $this->cleanPackage($event->getOperation()->getTargetPackage());
  }

  /**
   * Clean a single package.
   *
   * This applies in the context of a package post-install or post-update event.
   *
   * @param \Composer\Package\PackageInterface $package
   *   The package to clean.
   */
  public function cleanPackage(PackageInterface $package) {
    $extra = $this->composer->getPackage()->getExtra();
    if (isset($extra['drupal-cleanup']) &&
      isset($extra['drupal-cleanup'][$package->getType()])) {
      $rules = $extra['drupal-cleanup'][$package->getType()];
      $this->io->writeError(sprintf(
        '%sCleaning: <info>%s</info>',
        str_repeat(' ', 4),
        $package->getName()
      ));
      $package_path = $this->composer->getInstallationManager()
        ->getInstallPath($package);
      $fs = new Filesystem();
      foreach ($rules as $rule) {
        $paths = glob($package_path . DIRECTORY_SEPARATOR . $rule, GLOB_ERR);
        if (is_array($paths)) {
          foreach ($paths as $path) {
            try {
              $fs->remove($path);
            }
            catch (\Throwable $e) {
              $this->io->writeError(\sprintf(
                '<info>%s:</info> Error occurred: %s',
                $package->getName(),
                $e->getMessage()
              ));
            }
          }
        }
      }
    }
  }

}
