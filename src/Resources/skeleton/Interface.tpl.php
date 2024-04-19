<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use Symfony\Component\DependencyInjection\Attribute\AutoconfigureTag;

#[AutoconfigureTag('<?= $tag_name ?>')]
interface <?= $class_name ?>
{
    public function getName(): string;
}