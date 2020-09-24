# drupal-cleanup
Composer plugin to remove files on Drupal packages

You can configure the plugin using the root package's composer.json extra field, like this:

    "extra": {
      "drupal-cleanup": {
        "drupal-core": [
          "modules/*/tests",
          "modules/*/src/Tests",
          "profiles/demo_umami",
          "profiles/*/tests",
          "profiles/*testing*"
        ],
        "drupal-module": [
          "tests",
          "src/Tests"
        ],
        "exclude": [
          "web/modules/contrib/devel/.spoons"
        ]
      }
    }
