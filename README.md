# Convert WordPress Posts to Gutenberg Blocks

A Symfony Console Command that create a command to generate Gutenberg Block formatted code based import XML file.

## To Use

```markdown
git clone git@github.com:thinktandem/convert-posts-gutenberg.git
cd convert-posts-gutenberg
composer install
bin/console generate:xml FILE_PATH
```

## Best Way to Generate XML File 

1. Download [WordPress All Export Plugin](https://www.wpallimport.com/export/)
2. Export your posts as a XML, but do the following:
  - In the content field select "Export the value returned by a PHP function"
  - Put transform in the first vox that has <?php ___ ($value) ?>
  - Use this function in the function box:
  
  ```php
  <?php 
    function transform($value) {
      return wpautop($value);
    }
  ?>
  ```
  
  - Save the function.
  
### Requirements

The file path is required. It will error out without it.

### Output

The final XML is generated within the output folder in this app.
