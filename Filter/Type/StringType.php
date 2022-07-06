<?php
/*
 * This file is part of the Austral Filter Bundle package.
 *
 * (c) Austral <support@austral.dev>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Austral\FilterBundle\Filter\Type;

use Austral\FilterBundle\Filter\Type\Base\FilterType;
use Austral\FormBundle\Field as FormField;

/**
 * Austral Filter String Type.
 * @author Matthieu Beurel <matthieu@austral.dev>
 * @final
 */
class StringType extends FilterType
{

  /**
   * @var string
   */
  protected string $formFieldDefault = FormField\TextField::class;

  /**
   * @var array|string[]
   */
  protected array $formFieldsAccepted = array(
    FormField\TextField::class,
    FormField\ColorPicker::class
  );

  /**
   * @param $fieldname
   * @param array $options
   *
   * @return $this
   * @throws \Exception
   */
  public static function create($fieldname, array $options = array()): StringType
  {
    return new self($fieldname, $options);
  }

  /**
   * TextField constructor.
   *
   * @param string $fieldname
   * @param array $options
   *
   * @throws \Exception
   */
  public function __construct($fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
  }

}