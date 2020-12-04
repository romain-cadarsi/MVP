<?php

namespace App\Controller\Admin;

use App\Entity\Image;
use App\Entity\MasterCategorie;
use EasyCorp\Bundle\EasyAdminBundle\Controller\AbstractCrudController;
use EasyCorp\Bundle\EasyAdminBundle\Field\AssociationField;
use EasyCorp\Bundle\EasyAdminBundle\Field\IdField;
use EasyCorp\Bundle\EasyAdminBundle\Field\ImageField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextareaField;
use EasyCorp\Bundle\EasyAdminBundle\Field\TextField;

class MasterCategorieCrudController extends AbstractCrudController
{
    public static function getEntityFqcn(): string
    {
        return MasterCategorie::class;
    }

    public function configureFields(string $pageName): iterable
    {
        return [
            IdField::new('id')->hideOnForm(),
            TextField::new('name'),
            TextareaField::new('description'),
            ImageField::new('image')->setUploadDir('/public'),
            AssociationField::new('sousCategories')
        ];
    }
}
