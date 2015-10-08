<?php

namespace Oro\Bundle\LocaleBundle\Form\Type;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Intl\Intl;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Translation\Translator;

class LocaleType extends AbstractType
{
    /** @var Translator */
    protected $translator;

    /** @var float */
    protected $minTranslationProgress;

    /**
     * @param Translator $translator
     * @param float      $minTranslationProgress
     */
    public function __construct(Translator $translator, $minTranslationProgress)
    {
        $this->translator             = $translator;
        $this->minTranslationProgress = (float) $minTranslationProgress;
    }

    /**
     * {@inheritdoc}
     */
    public function configureOptions(OptionsResolver $resolver)
    {
        $availableLocales = Intl::getLocaleBundle()->getLocaleNames('en');
        $locales = [];

        $mainReference = count($this->translator->getMessages('en'), COUNT_RECURSIVE);
        $minTranslations = $mainReference * $this->minTranslationProgress;

        foreach ($availableLocales as $code => $locale) {
            if (preg_match('/^[a-z]{2}$/i', $code)) {
                $reference = count($this->translator->getMessages($code), COUNT_RECURSIVE);
                if ($reference >= $minTranslations) {
                    $locales[$code] = $locale;
                }
            }
        }

        $resolver->setDefaults(['choices' => $locales]);
    }

    /**
     * {@inheritdoc}
     */
    public function getParent()
    {
        return 'locale';
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return 'oro_locale';
    }
}
