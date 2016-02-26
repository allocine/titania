<?php

namespace Allocine\Titania\Type\Configuration;

use Symfony\Component\OptionsResolver\OptionsResolver;

/**
 * ClassDefinition is container for configuration
 *
 * @author Xavier HAUSHERR <xhausherr@allocine.fr>
 */
class ClassDefinition
{
    /**
     * @var array
     */
    protected $configuration = [];

    /**
     *
     * @param array $configuration
     * @throws \Exception
     */
    public function __construct(array $configuration)
    {
        $resolver = new OptionsResolver();

        $resolver
            ->setDefaults([
                'nullable' => false,
                'class'    => null,
            ])
            ->setRequired('class')
            ->setAllowedTypes('class', 'string')
        ;

        $this->configuration = $resolver->resolve($configuration);
    }

    /**
     *
     * @return string
     */
    public function getClass()
    {
        return $this->configuration['class'];
    }

    /**
     *
     * @return boolean
     */
    public function isNullable()
    {
        return $this->configuration['nullable'];
    }
}
