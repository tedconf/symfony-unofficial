<?php

/*
 * This file is part of the symfony package.
 * (c) 2004-2006 Fabien Potencier <fabien.potencier@symfony-project.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

/*
 * NOTE: Symfony tone support is based on
 * Garden (http://garden.tigris.org), Copyright 2006 Tomas Varaneckas
 * which is originally licensed under the Apache License, Version 2.0
 * You may obtain a copy of that license at http://www.apache.org/licenses/LICENSE-2.0
 *
 * However, by totally refactoring it to symfony needs it is now shipped
 * with symfony under the license you can find in the LICENSE
 * file that was distributed with this source code.
 *
 * If you have any questions about the symfony tone implementation,
 * please consider to not contact the original author of Garden
 * but ask on the symfony mailing list.
 */

/**
 * Tone factory interface
 *
 * It is the end-user oriented interface, which defines several methods for
 * work with symfony tones.
 *
 * @author Matthias Nothhaft <matthias.nothhaft@googlemail.com>
 * @author Tomas Varaneckas <tomas [dot] varaneckas [at] gmail [dot] com>
 */
interface sfToneFactory
{
  /**
   * This should initialize the factory
   */
  public function initialize();

  /**
   * This should retrieve a Tone
   *
   * If necessary, Tone should be created during the process.
   *
   * @param  string Tone id or alias
   * @return mixed Tone object
   */
  public function getTone($name);

  /**
   * A verification method to see if Tone definition exists in the factory
   *
   * @param  string Tone id or alias
   * @return boolean
   */
  public function containsTone($name);

  /**
   * A getter for Tone aliases
   *
   * Should return an array of all possible Tone names.
   *
   * @param  String Tone id or alias
   * @return Array of String
   */
  public function getAliases($name);

  /**
   * Will tell if a Tone is singleton or not
   *
   * @param  string Tone id or alias
   * @return boolean
   */
  public function isSingleton($name);

  /**
   * Will remove Tone instance from the factory
   *
   * This also includes removal of aliases and definition, and the execution
   * of destroy methods, if such will be defined.
   *
   * @param string Tone id or alias
   */
  public function removeTone($name);

  /**
   * Will remove all Tone instances from the factory
   */
  public function shutdown();

}
