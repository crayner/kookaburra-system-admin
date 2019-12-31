<?php
/**
 * Created by PhpStorm.
 *
 * kookaburra
 * (c) 2019 Craig Rayner <craig@craigrayner.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 *
 * User: craig
 * Date: 2/10/2019
 * Time: 13:37
 */

namespace Kookaburra\SystemAdmin\Form\Transform;

use App\Provider\ProviderFactory;
use Doctrine\Common\Collections\ArrayCollection;
use Kookaburra\SystemAdmin\Form\Entity\ImportColumn;
use Symfony\Component\Form\DataTransformerInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\YamlEncoder;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * Class ColumnsToStringTransformer
 * @package App\Form\Transform
 */
class ColumnsToStringTransformer implements DataTransformerInterface
{
    /**
     * @var Serializer
     */
    private $serialiser;

    /**
     * @return Serializer
     */
    public function getSerialiser(array $defaultContext = []): Serializer
    {
        $normalizer = new GetSetMethodNormalizer(null, null, null, null, null, $defaultContext);

        return $this->serialiser = $this->serialiser ?: new Serializer([new ArrayDenormalizer(), $normalizer], [new JsonEncoder(), new YamlEncoder()]);
    }

    /**
     * transform
     * @param mixed $value
     * @return bool|float|int|mixed|string
     */
    public function transform($value)
    {
        if (null === $value || '' === $value)
            return $value;
        if ($value instanceof ArrayCollection) {
            foreach ($value as $column) {
                if (is_object($column->getText()) && get_class($column->getText()) && method_exists($column->getText(), 'getId')) {
                    $column->setTextObjectName(get_class($column->getText()));
                    $column->setText($column->getText()->getId());
                }
            }

            return $this->getSerialiser()->serialize($value, 'json');
        }
        return $value;
    }

    /**
     * reverseTransform
     * @param mixed $value
     * @return array|mixed|object
     */
    public function reverseTransform($value)
    {
        if (null === $value || '' === $value)
            return $value;

       if (is_string($value)) {
            $value = $this->getSerialiser()->deserialize($value, ImportColumn::class . '[]', 'json');
            $value = new ArrayCollection($value ?: []);

            foreach($value as $column)
            {
                if (null !== $column->getTextObjectName()) {
                    $column->setText(ProviderFactory::getRepository($column->getTextObjectName())->find($column->getText()));
                    $column->setTextObjectName(null);
                }
            }
        }

        return $value;
    }
}