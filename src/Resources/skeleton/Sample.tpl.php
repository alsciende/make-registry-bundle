<?= "<?php\n" ?>

namespace <?= $namespace ?>;

class <?= $class_name ?> implements <?= $interface_name ?>
{
    public function getName(): string
    {
        return 'sample';
    }
}