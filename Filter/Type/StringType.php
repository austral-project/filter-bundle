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
 * @template T of object
 *
 * @extends FilterType<T>
 *
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
   * @var array<int|string, mixed>
   */
  protected array $formFieldsAccepted = array(
    FormField\TextField::class,
    FormField\ColorPicker::class
  );

  /**
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @return StringType<T>
   * @throws \Exception
   */
  public static function create(string $fieldname, array $options = array()): self
  {
    /** @var StringType<T> $element */
    $element = new self($fieldname, $options);
    return $element;
  }

  /**
   * TextField constructor.
   *
   * @param string $fieldname
   * @param array<string, mixed> $options
   *
   * @throws \Exception
   */
  public function __construct(string $fieldname, array $options = array())
  {
    parent::__construct($fieldname, $options);
  }

}