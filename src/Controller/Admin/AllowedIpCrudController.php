<?php

namespace App\Controller\Admin;

use App\Entity\AllowedIp;
use EasyCorp\Bundle\EasyAdminBundle\Config\Crud;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;

class AllowedIpCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return AllowedIp::class;
    }

    public function configureCrud(Crud $crud): Crud
    {
        return $crud
            ->setEntityLabelInSingular('Allowed IP')
            ->setEntityLabelInPlural('Allowed IPs')
        ;
    }
}
