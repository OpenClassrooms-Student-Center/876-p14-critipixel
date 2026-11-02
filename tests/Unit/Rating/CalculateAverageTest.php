<?php

declare(strict_types=1);

namespace App\Tests\Rating;

use App\Model\Entity\Review;
use App\Model\Entity\VideoGame;
use App\Rating\RatingHandler;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class CalculateAverageTest extends TestCase
{
    /**
     * Teste le calcul de la moyenne des notes pour un jeu vidéo avec avis.
     * @param int[] $ratings Tableau de notes pour le jeu vidéo
     * @param int $expectedAverage Moyenne attendue (arrondie avec ceil)
     *
     */
    #[DataProvider('provideVideoGameRatingsData')]
    public function testCalculateAverageWithReviews(array $ratings, int $expectedAverage): void
    {
        // 1. Arrange : Création du jeu de données
        $videoGame = new VideoGame();

        foreach ($ratings as $ratingValue) {
            // On mock l'entité Review pour simuler différentes notes
            $review = $this->createMock(Review::class);
            $review->method('getRating')->willReturn($ratingValue);

            // On ajoute l'avis à la collection du VideoGame
            $videoGame->getReviews()->add($review);
        }

        $handler = new RatingHandler();

        // 2. Act : Exécution de la méthode à tester
        $handler->calculateAverage($videoGame);

        // 3. Assert : Vérification du résultat attendu (avec le ceil)
        self::assertSame($expectedAverage, $videoGame->getAverageRating());
    }

    /**
     * Fournit 5 cas de test différents (notes -> moyenne attendue avec ceil)
     *
     * @return array<string, array{ratings: int[], expectedAverage: int}>
     */
    public static function provideVideoGameRatingsData(): array
    {
        return [
            'Cas 1 : Moyenne avec arrondi supérieur' => [
                'ratings' => [3, 4], // Somme = 7, Moyenne = 3.5 -> ceil(3.5) = 4
                'expectedAverage' => 4,
            ],
            'Cas 2 : Moyenne entière exacte' => [
                'ratings' => [1, 2, 3], // Somme = 6, Moyenne = 2.0 -> ceil(2.0) = 2
                'expectedAverage' => 2,
            ],
            'Cas 3 : Un seul avis unique' => [
                'ratings' => [3], // Somme = 3, Moyenne = 3.0 -> ceil(3.0) = 3
                'expectedAverage' => 3,
            ],
            'Cas 4 : Moyenne fractionnaire complexe' => [
                'ratings' => [2, 2, 3], // Somme = 7, Moyenne = 2.33... -> ceil = 3
                'expectedAverage' => 3,
            ],
            'Cas 5 : Notes majoritairement hautes' => [
                'ratings' => [5, 5, 5, 4], // Somme = 19, Moyenne = 4.75 -> ceil = 5
                'expectedAverage' => 5,
            ],
        ];
    }

}
