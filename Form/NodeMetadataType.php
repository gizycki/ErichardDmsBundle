<?php

namespace Erichard\DmsBundle\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;

class NodeMetadataType extends AbstractType
{
    protected $em;

    public function __construct($em)
    {
        $this->em = $em;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $metadatas = $this->em->getRepository('Erichard\DmsBundle\Entity\Metadata')->findByScope(array('node', 'both'));

        foreach ($metadatas as $m) {
            $builder->add($m->getName(), $m->getType(), array(
                'label' => $m->getLabel(),
                'required' => $m->isRequired(),
            ));
        }
    }

    public function getName()
    {
        return 'node_metadata';
    }
}
