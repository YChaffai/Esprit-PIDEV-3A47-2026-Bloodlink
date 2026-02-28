<?php

namespace App\Twig;

use Stichoza\GoogleTranslate\GoogleTranslate;
use Symfony\Contracts\Cache\CacheInterface;
use Symfony\Contracts\Cache\ItemInterface;
use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;

class GoogleTranslateExtension extends AbstractExtension
{
    private $cache;

    // On injecte le système de Cache de Symfony
    public function __construct(CacheInterface $cache)
    {
        $this->cache = $cache;
    }

    public function getFilters(): array
    {
        return [
            // On crée notre propre filtre Twig qu'on appellera "gtrans"
            new TwigFilter('gtrans', [$this, 'translateText']),
        ];
    }

    public function translateText(string $text, string $targetLang = 'fr'): string
    {
        // Si la langue est le français (langue de base) ou le texte est vide, on ne traduit pas
        if ($targetLang === 'fr' || empty(trim($text))) {
            return $text;
        }

        // On crée une clé de cache unique pour cette phrase et cette langue
        $cacheKey = 'gtrans_' . md5($text . '_' . $targetLang);

        // Le cache vérifie s'il connaît déjà la traduction. Sinon, il appelle l'API !
        return $this->cache->get($cacheKey, function (ItemInterface $item) use ($text, $targetLang) {
            $item->expiresAfter(3600 * 24 * 7); // On garde la traduction en mémoire pendant 7 jours
            
            $tr = new GoogleTranslate();
            $tr->setSource('fr');
            $tr->setTarget($targetLang);
            
            return $tr->translate($text);
        });
    }
}