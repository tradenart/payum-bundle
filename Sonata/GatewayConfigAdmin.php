<?php

namespace Payum\Bundle\PayumBundle\Sonata;

use Payum\Core\Model\GatewayConfig;
use Payum\Core\Security\CryptedInterface;
use Payum\Core\Security\CypherInterface;
use Sonata\AdminBundle\Admin\AbstractAdmin;
use Sonata\AdminBundle\Datagrid\ListMapper;
use Sonata\AdminBundle\Form\FormMapper;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormFactoryInterface;
use Payum\Core\Bridge\Symfony\Form\Type\GatewayConfigType;
use Symfony\Component\Form\FormInterface;

class GatewayConfigAdmin extends AbstractAdmin
{
    protected FormFactoryInterface $formFactory;
    protected ?CypherInterface $cypher = NULL;

    public function setFormFactory(FormFactoryInterface $formFactory): void
    {
        $this->formFactory = $formFactory;
    }

    public function setCypher(CypherInterface $cypher): void
    {
        $this->cypher = $cypher;
    }

    /**
     * {@inheritdoc}
     */
    public function preUpdate($object): void
    {
        parent::preUpdate($object);

        if ($this->cypher && $object instanceof CryptedInterface) {
            $object->encrypt($this->cypher);
        }
    }

    /**
     * @var GatewayConfig $object
     */
    public function prePersist($object): void
    {

        parent::prePersist($object);

        /**
         * @var GatewayConfig $data
         */
        $data = $this->getForm()->get('gateway')->getData();
        $object->setFactoryName($data->getFactoryName());
        $object->setGatewayName($data->getGatewayName());
        $object->setConfig($data->getConfig());


        if ($this->cypher && $object instanceof CryptedInterface) {
            $object->encrypt($this->cypher);
        }
    }

    /**
     * {@inheritdoc}
     */
    protected function configureFormFields(FormMapper $form): void
    {
        if ($this->hasSubject()) {
            $data = $this->getSubject();
        } else {
            $data = null;
        }
        $form->add('gateway', GatewayConfigType::class, [
            'mapped' => false,
            'data' => $data
        ]);
    }

    /**
     * {@inheritDoc}
     */
    protected function configureListFields(ListMapper $list): void
    {
        $list
            ->add('gatewayName')
            ->add('factoryName')
            ->add('config', 'array')
            ->add('_action', 'actions', array(
                'actions' => array(
                    'edit' => array(),
                    'delete' => array(),
                )
            ));
    }
}
