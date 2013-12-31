<?php
/**
 * Textpress - PHP Flat file blog engine
 * Textpress is a flat file blog engine, built on top of Slim inspired from Toto. 
 * Now it have only a limited set of features and url options.
 * 
 * @author      Shameer C <me@shameerc.com>
 * @copyright   2012 - Shameer C
 * @version     1.0
 *
 * MIT LICENSE
 *
 * Permission is hereby granted, free of charge, to any person obtaining
 * a copy of this software and associated documentation files (the
 * "Software"), to deal in the Software without restriction, including
 * without limitation the rights to use, copy, modify, merge, publish,
 * distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to
 * the following conditions:
 *
 * The above copyright notice and this permission notice shall be
 * included in all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND,
 * EXPRESS OR IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF
 * MERCHANTABILITY, FITNESS FOR A PARTICULAR PURPOSE AND
 * NONINFRINGEMENT. IN NO EVENT SHALL THE AUTHORS OR COPYRIGHT HOLDERS BE
 * LIABLE FOR ANY CLAIM, DAMAGES OR OTHER LIABILITY, WHETHER IN AN ACTION
 * OF CONTRACT, TORT OR OTHERWISE, ARISING FROM, OUT OF OR IN CONNECTION
 * WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE SOFTWARE.
 */
namespace Textpress;

/**
* Tag
* Represents a Tag with name and count 
*
* @author       Shameer
* @since        1.0 
*/
class Tag
{
    /**
    * tag name 
    *
    * @var string
    */
    public $name;

    /**
    * number of occurances of a tag 
    *
    * @var int
    */
    public $count;

    /**
    * Constructor 
    * 
    * @param string $name  Tag name
    * @param int $count  Number of occurances of a tag
    */
    public function __construct($name, $count=1)
    {
        $this->name = $name;
        $this->count = $count;
    }
}