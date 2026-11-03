<?php

declare(strict_types=1);

namespace App\Tests\Functional\Review;

use App\Model\Entity\Review;
use App\Tests\Functional\FunctionalTestCase;
use Symfony\Component\HttpFoundation\Response;

final class AddReviewTest extends FunctionalTestCase
{
    private const VIDEO_GAME_URL = '/jeu-video-1';
    private const VIDEO_GAME_H1 = 'Jeu vidéo 1';
    private const VIDEO_GAME_H2 = 'Avis des lecteurs';
    private const TEST_USER_EMAIL = 'user+4@email.com';
    private const TEST_COMMENT = 'Mon commentaire de test PHPUnit';
    
    protected function tearDown(): void
    {
        $this->removeTestReview();
        parent::tearDown();
    }

    // test d'ajout d'un avis
    public function testAddReview(): void
    {
        // Nettoie un éventuel avis créé lors d'un run précédent
        $this->removeTestReview();

        // Connecte un utilisateur qui n'a pas encore posté d'avis
        $this->login(self::TEST_USER_EMAIL);

        // Accède à la page du jeu vidéo
        $crawler = $this->get(self::VIDEO_GAME_URL);

        // Vérifie que la page est accessible
        self::assertResponseIsSuccessful();

        // Vérifie que le titre de la page est correct
        self::assertSelectorTextContains('h1', self::VIDEO_GAME_H1);

        // Vérifie que le titre de l'onglet est correct
        self::assertAnySelectorTextContains('h2', self::VIDEO_GAME_H2);

        // Vérifie la présence du formulaire nommé "review"
        self::assertSelectorExists('form[name="review"]', 'Ce User a déjà déposé un avis pour ce jeu');

        // Soumet le formulaire avec la note 3 et le commentaire
        $this->client->submitForm('Poster', [
            'review[rating]' => 3,
            'review[comment]' => self::TEST_COMMENT,
        ]);

        // Vérifie la redirection vers la page du jeu vidéo avec le code HTTP 302
        self::assertResponseRedirects(self::VIDEO_GAME_URL, Response::HTTP_FOUND);

        // Suit la redirection
        $this->client->followRedirect();

        // Vérifie que la page après redirection est accessible
        self::assertResponseIsSuccessful();

        // Vérifie que le commentaire est bien affiché dans la liste des avis
        self::assertSelectorTextContains('.list-group', self::TEST_COMMENT);

        // Vérifie que le formulaire n'est plus accessible (un seul avis par jeu)
        self::assertSelectorNotExists('form[name="review"]');

        // Vérifie en base de données que l'avis a bien été enregistré
        $review = $this->getEntityManager()->getRepository(Review::class)->findOneBy([
            'comment' => self::TEST_COMMENT,
        ]);
        self::assertNotNull($review);
        self::assertSame(3, $review->getRating());
    }

    // test d'ajout d'un avis avec une note hors de portée
    public function testAddReviewOutOfRange(): void
    {
        // Nettoie un éventuel avis créé lors d'un run précédent
        $this->removeTestReview();

        // Connecte un utilisateur qui n'a pas encore posté d'avis
        $this->login(self::TEST_USER_EMAIL);

        // Accède à la page du jeu vidéo
        $this->get(self::VIDEO_GAME_URL);

        // Vérifie que la page est accessible
        self::assertResponseIsSuccessful();

        // Vérifie la présence du formulaire nommé "review"
        self::assertSelectorExists('form[name="review"]', 'Ce User a déjà déposé un avis pour ce jeu');

        // Soumet la requête HTTP directement pour contourner la validation
        // côté client du DomCrawler (qui rejette les valeurs hors des options du ChoiceType)
        $this->client->request('POST', self::VIDEO_GAME_URL, [
            'review' => [
                'rating' => 6,
                'comment' => self::TEST_COMMENT,
                '_token' => $this->client->getCrawler()->filter('form[name="review"] input[name="review[_token]"]')->attr('value'),
            ],
        ]);

        // Vérifie que le formulaire invalide retourne une erreur 422
        self::assertResponseIsUnprocessable();

        // Vérifie que le formulaire est à nouveau affiché (pas de redirection)
        self::assertSelectorExists('form[name="review"]');
    }

    // test d'ajout d'un avis sans authentification
    public function testAddReviewWithoutAuth(): void
    {
        // 1. Accède à la page du jeu vidéo en tant qu'invité (non connecté)
        $this->client->request('GET', self::VIDEO_GAME_URL);

        // Vérifie que la page répond correctement (200 OK)
        self::assertResponseIsSuccessful(); 

        // 2. Vérifie que le formulaire d'avis n'est pas accessible/affiché dans le HTML
        self::assertSelectorNotExists(
            'form[name="review"]',
            'Le formulaire d\'avis ne devrait pas être visible pour un utilisateur non connecté.'
        );

        // 3. Tente de contourner l'interface en envoyant directement une requête POST 
        // sur l'URL du jeu vidéo avec les données du formulaire
        $this->client->request('POST', '/jeu-video-1', [
            'review' => [
                'rating' => 4,
                'comment' => 'Tentative de hack / test non autorisé',
            ],
        ]);

        // 4. Vérifie que Symfony redirige bien vers la page de connexion
        self::assertResponseRedirects('/auth/login');
    }

    // test d'ajout d'un avis avec un rating null
    public function testAddReviewNull(): void
    {
        // Désactive la capture automatique des exceptions par le client Symfony
        // pour permettre à PHPUnit de l'attraper
        $this->client->catchExceptions(false);
        
        // Déclare à PHPUnit qu'on s'attend à une exception de type DriverException ou PDOException
        // (Selon la configuration Doctrine, les erreurs SQL lèvent généralement une PDOException ou une DriverException de Doctrine)
        $this->expectException(\Doctrine\DBAL\Exception\NotNullConstraintViolationException::class);
        
        // On peut aussi affiner le message attendu :
        //$this->expectExceptionMessage("SQLSTATE[23000]: Integrity constraint violation: 1048 Column 'rating' cannot be null");

        // Connecte un utilisateur qui n'a pas encore posté d'avis
        $this->login(self::TEST_USER_EMAIL);

        // Accède à la page du jeu vidéo
        $this->client->request('GET', self::VIDEO_GAME_URL);

        // Vérifie que la page est accessible
        self::assertResponseIsSuccessful();

        // Vérifie la présence du formulaire nommé "review"
        self::assertSelectorExists('form[name="review"]', 'Ce User a déjà déposé un avis pour ce jeu');

        // Soumet la requête HTTP avec un rating vide (ce qui va déclencher l'insertion en base et l'exception SQL)
        $this->client->request('POST', self::VIDEO_GAME_URL, [
            'review' => [
                'rating' => '',
                'comment' => self::TEST_COMMENT,
                '_token' => $this->client->getCrawler()->filter('form[name="review"] input[name="review[_token]"]')->attr('value'),
            ],
        ]);

    }

        // test d'ajout d'un avis sans authentification
    public function testAccessFormWithoutAuth(): void
    {
        // 1. Accède à la page du jeu vidéo en tant qu'invité (non connecté)
        $this->client->request('GET', self::VIDEO_GAME_URL);

        // Vérifie que la page répond correctement (200 OK)
        self::assertResponseIsSuccessful(); 

        // 2. Vérifie que le formulaire d'avis n'est pas accessible/affiché dans le HTML
        self::assertSelectorNotExists(
            'form[name="review"]',
            'Le formulaire d\'avis ne devrait pas être visible pour un utilisateur non connecté.'
        );
    }

    // Méthode pour supprimer un avis de test
    private function removeTestReview(): void
    {
        $entityManager = $this->getEntityManager();
        $review = $entityManager->getRepository(Review::class)->findOneBy([
            'comment' => self::TEST_COMMENT,
        ]);

        if ($review !== null) {
            $entityManager->remove($review);
            $entityManager->flush();
        }
    }
}
