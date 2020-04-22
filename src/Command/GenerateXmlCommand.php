<?php

namespace App\Command;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class GenerateXmlCommand extends Command {

  /**
   * @var string
   */
  protected static $defaultName = 'generate:xml';

  /**
   * @var string
   */
  protected $fileName;

  /**
   * @var string
   */
  protected $contents;

  /**
   * @var string
   */
  protected $finalFileName;

  /**
   * @var string
   */
  protected $finalContent = '';

  /**
   * @inheritdoc
   */
  protected function configure() {
      $this
        ->setDescription('Generate Gutenberg Block formatted code based import XML file')
        ->addArgument('filename', InputArgument::REQUIRED, 'Path to XML File (ex: ./fancy.xml');
  }

  /**
   * @inheritdoc
   */
  protected function execute(InputInterface $input, OutputInterface $output) {
    $io = new SymfonyStyle($input, $output);
    if ($this->fileName = $input->getArgument('filename')) {
      // Check if XML.
      if (!$this->validateExtension()) {
        $io->error("Your file is not a XML file");
        return;
      }

      // Engage.
      $this->contents = file_get_contents($this->fileName);
      $this->createDirectoryStructureAndFile();
      $this->processRows();

      // Generate the file.
      if (!empty($this->finalContent)) {
        $this->generateContent();
        $io->success("File generated.");
        return;
      }

      $io->error("Error occured while processing");
      return;
    }

    $io->error("Please provide a filename or pipe template content to STDIN.");
  }

  /**
   * Check is the file is XML.
   *
   * @return bool
   */
  protected function validateExtension() {
    return pathinfo($this->fileName, PATHINFO_EXTENSION) === 'xml';
  }

  /**
   * Creates the directory and output file.
   *
   * @return void
   */
  protected function createDirectoryStructureAndFile() {
    // We set the path to be all folder so urls don't have html on them.
    $output = './output';

    // Create the directories as need be.
    if (!is_dir($output)){
      mkdir($output, 0755, true);
    }

    // Now create our file.
    $this->finalFileName = $output . '/final.xml';
    if (!file_exists($this->finalFileName)) {
      $file = fopen($this->finalFileName, 'wb');
      fwrite($file, '');
      fclose($file);
    }
  }

  /**
   * Processes the Rows of stuffs.
   *
   * @return void
   */
  protected function processRows() {
    $document = new \DOMDocument();
    $document->loadXml($this->contents, LIBXML_PARSEHUGE);
    /** @var \DomElement $node */
    foreach ($document->getElementsByTagName("Content") as $node) {
      $content = $this->addGutenbergTags($node->nodeValue);
      $node->nodeValue = '';
      $node->appendChild($document->createCDATASection($content));
    }
    $this->finalContent = $document->saveXML();
  }

  /**
   * Generate the front matter.
   *
   * @param string
   */
  protected function addGutenbergTags($content) {
    $tags = ["<p>", "<h1>", "<h2>", "<h3>", "<h4>", "<h5>", "<h6>"];
    foreach ($tags as $tag) {
      if (strpos($content, $tag) !== FALSE) {
        $end = str_replace("<", "</", $tag);
        $open_wrapper = $tag === "<p>" ? "<!– wp:paragraph –>" : "<!– wp:heading –>";
        $closing_wrapper = $tag === "<p>" ? "<!– /wp:paragraph –>" : "<!– /wp:heading –>";
        $content = str_replace($tag, $open_wrapper . $tag, $content);
        $content = str_replace($end, $end . $closing_wrapper, $content);
      }
    }
    return $content;
  }

  /**
   * Converts our html to markdown.
   *
   * @return void
   */
  protected function generateContent() {
    // Now write the body content.
    $file = fopen($this->finalFileName, 'wb');
    fwrite($file, $this->finalContent);
    fclose($file);
  }
}
