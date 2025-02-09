<?php

namespace App\Tests\Controller;

use App\Entity\Candidature;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\KernelBrowser;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

final class CandidatureControllerTest extends WebTestCase
{
    private KernelBrowser $client;
    private EntityManagerInterface $manager;
    private EntityRepository $candidatureRepository;
    private string $path = '/candidature/';

    protected function setUp(): void
    {
        $this->client = static::createClient();
        $this->manager = static::getContainer()->get('doctrine')->getManager();
        $this->candidatureRepository = $this->manager->getRepository(Candidature::class);

        foreach ($this->candidatureRepository->findAll() as $object) {
            $this->manager->remove($object);
        }

        $this->manager->flush();
    }

    public function testIndex(): void
    {
        $this->client->followRedirects();
        $crawler = $this->client->request('GET', $this->path);

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Candidature index');

        // Use the $crawler to perform additional assertions e.g.
        // self::assertSame('Some text on the page', $crawler->filter('.p')->first());
    }

    public function testNew(): void
    {
        $this->markTestIncomplete();
        $this->client->request('GET', sprintf('%snew', $this->path));

        self::assertResponseStatusCodeSame(200);

        $this->client->submitForm('Save', [
            'candidature[statut]' => 'Testing',
            'candidature[dateApplied]' => 'Testing',
            'candidature[rating]' => 'Testing',
            'candidature[idTravailleur]' => 'Testing',
            'candidature[idOffre]' => 'Testing',
        ]);

        self::assertResponseRedirects($this->path);

        self::assertSame(1, $this->candidatureRepository->count([]));
    }

    public function testShow(): void
    {
        $this->markTestIncomplete();
        $fixture = new Candidature();
        $fixture->setStatut('My Title');
        $fixture->setDateApplied('My Title');
        $fixture->setRating('My Title');
        $fixture->setIdTravailleur('My Title');
        $fixture->setIdOffre('My Title');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));

        self::assertResponseStatusCodeSame(200);
        self::assertPageTitleContains('Candidature');

        // Use assertions to check that the properties are properly displayed.
    }

    public function testEdit(): void
    {
        $this->markTestIncomplete();
        $fixture = new Candidature();
        $fixture->setStatut('Value');
        $fixture->setDateApplied('Value');
        $fixture->setRating('Value');
        $fixture->setIdTravailleur('Value');
        $fixture->setIdOffre('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s/edit', $this->path, $fixture->getId()));

        $this->client->submitForm('Update', [
            'candidature[statut]' => 'Something New',
            'candidature[dateApplied]' => 'Something New',
            'candidature[rating]' => 'Something New',
            'candidature[idTravailleur]' => 'Something New',
            'candidature[idOffre]' => 'Something New',
        ]);

        self::assertResponseRedirects('/candidature/');

        $fixture = $this->candidatureRepository->findAll();

        self::assertSame('Something New', $fixture[0]->getStatut());
        self::assertSame('Something New', $fixture[0]->getDateApplied());
        self::assertSame('Something New', $fixture[0]->getRating());
        self::assertSame('Something New', $fixture[0]->getIdTravailleur());
        self::assertSame('Something New', $fixture[0]->getIdOffre());
    }

    public function testRemove(): void
    {
        $this->markTestIncomplete();
        $fixture = new Candidature();
        $fixture->setStatut('Value');
        $fixture->setDateApplied('Value');
        $fixture->setRating('Value');
        $fixture->setIdTravailleur('Value');
        $fixture->setIdOffre('Value');

        $this->manager->persist($fixture);
        $this->manager->flush();

        $this->client->request('GET', sprintf('%s%s', $this->path, $fixture->getId()));
        $this->client->submitForm('Delete');

        self::assertResponseRedirects('/candidature/');
        self::assertSame(0, $this->candidatureRepository->count([]));
    }
}
