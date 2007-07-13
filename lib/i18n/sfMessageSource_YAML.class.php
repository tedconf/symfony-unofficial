<?php

/**
 * This message source driver enables to use yml files instead of XLIFF!
 *
 * Yml format is as follows (de):
 * <code>
 * my_label_x:
 *   target: 'Mein Label x'
 * 'This is a message.':
 *   target: 'Das ist eine Nachricht.'
 *   note: This field can be used for notes as with XLIFF.
 * </code>
 *
 * I chose to set the extension to .i18n.yml because my editor
 * is then able to automatically use UTF-8 encoding.
 *
 * @author Matthias Nothhaft <matthias.nothhaft@googlemail.com>
 * @NOTE update() and delete() are not supported, yet.
 */
class sfMessageSource_YAML extends sfMessageSource_XLIFF
{
  /**
   * Message data filename extension.
   * @var string
   */
  protected $dataExt = '.i18n.yml';

  /**
   * Load the messages from a XLIFF file.
   *
   * @param string yml file.
   * @return array of messages.
   */
  protected function &loadData($filename)
  {
    $contents = sfYaml::load($filename);

    $translations = array();

    $id = 1;
    foreach ($contents as $source => $entry)
    {
      $translations[$source][] = $entry['target'];
      $translations[$source][] = (string)$id++;
      $translations[$source][] = !empty($entry['note']) ? $entry['note'] : '';
    }

    return $translations;
  }

  /**
   * Save the list of untranslated blocks to the translation source. 
   * If the translation was not found, you should add those
   * strings to the translation source via the <b>append()</b> method.
   *
   * @param string the catalogue to add to
   * @return boolean true if saved successfuly, false otherwise.
   */
  public function save($catalogue = 'messages')
  {
    $messages = $this->untranslated;
    if (count($messages) <= 0)
    {
      return false;
    }

    $variants = $this->getVariants($catalogue);
    if ($variants)
    {
      list($variant, $filename) = $variants;
    }
    else
    {
      return false;
    }

    if (is_writable($filename) == false)
    {
      throw new sfException("Unable to save to file {$filename}, file must be writable.");
    }

    $contents = file_get_contents($filename);
    $contents .= "\n# untranslated -- " . @date('Y-m-d H:i:s') . "\n";

    // for each message add it to the i18n.yml file
    foreach ($messages as $message)
    {
        $contents .= '"' . $message . "\":\n" . '  ' . $variant . ": \n";
    }

    // save it and clear the cache for this variant
    file_put_contents($filename, $contents);
    if (!empty($this->cache))
    {
      $this->cache->clean($variant, $this->culture);
    }

    return true;
  }

  /**
   * Update the translation.
   *
   * @param string the source string.
   * @param string the new translation string.
   * @param string comments
   * @param string the catalogue to save to.
   * @return boolean true if translation was updated, false otherwise.
   */
  public function update($text, $target, $comments, $catalogue = 'messages')
  {
    throw new sfException('Implement me!');

    $variants = $this->getVariants($catalogue);
    if ($variants)
    {
      list($variant, $filename) = $variants;
    }
    else
    {
      return false;
    }

    if (is_writable($filename) == false)
    {
      throw new sfException("Unable to update file {$filename}, file must be writable.");
    }


    // TODO: load existing translation and update it
    // maybe use loadYaml(), etc. !?


    $contents = file_get_contents($filename);

    if (file_put_contents($filename, $contents))
    {
      if (!empty($this->cache))
      {
        $this->cache->clean($variant, $this->culture);
      }

      return true;
    }

    return false;
  }

  /**
   * Delete a particular message from the specified catalogue.
   *
   * @param string the source message to delete.
   * @param string the catalogue to delete from.
   * @return boolean true if deleted, false otherwise. 
   */
  public function delete($message, $catalogue='messages')
  {
    throw new sfException('delete is not supported, yet');

    $variants = $this->getVariants($catalogue);
    if ($variants)
    {
      list($variant, $filename) = $variants;
    }
    else
    {
      return false;
    }

    if (is_writable($filename) == false)
    {
      throw new sfException("Unable to modify file {$filename}, file must be writable.");
    }

    // TODO
  }

  protected function getTemplate($catalogue)
  {
    $date = date('c');

    return <<<EOD
# auto-generated i18n YAML file
# source language: EN
# target language: {$this->culture}
# catalogue:       $catalogue
# date:            $date

EOD;
  }

}
