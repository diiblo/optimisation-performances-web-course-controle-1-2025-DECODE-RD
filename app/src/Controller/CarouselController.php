<?php

namespace App\Controller;

use App\Repository\DirectusFilesRepository;
use App\Repository\GalaxyRepository;
use App\Repository\ModelesFilesRepository;
use App\Repository\ModelesRepository;
use Liip\ImagineBundle\Imagine\Cache\CacheManager;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class CarouselController extends AbstractController
{
    #[Route('/carousel', name: 'app_carousel')]
    public function index(GalaxyRepository $galaxyRepository, ModelesRepository $modelesRepository, ModelesFilesRepository $modelesFilesRepository, DirectusFilesRepository $directusFilesRepository, CacheManager $cacheManager): Response
    {
        $galaxies = $galaxyRepository->findAll();
        $carousel = [];

        $modeleIds = [];
        foreach ($galaxies as $galaxy) {
            $modeleId = $galaxy->getModele();
            if ($modeleId !== null && $modeleId !== '') {
                $modeleIds[] = $modeleId;
            }
        }
        $modeleIds = array_values(array_unique($modeleIds));

        $modeleIdSet = [];
        if ($modeleIds !== []) {
            $modeles = $modelesRepository->findBy(['id' => $modeleIds]);
            foreach ($modeles as $modele) {
                $id = $modele->getId();
                if ($id !== null && $id !== '') {
                    $modeleIdSet[$id] = true;
                }
            }
        }

        $modelesFiles = $modeleIdSet === []
            ? []
            : $modelesFilesRepository->findBy(['modeles_id' => array_keys($modeleIdSet)]);

        $fileIdsByModeleId = [];
        $allFileIds = [];
        foreach ($modelesFiles as $modelesFile) {
            $modeleId = $modelesFile->getModelesId();
            $fileId = $modelesFile->getDirectusFilesId();
            if ($modeleId === null || $modeleId === '' || $fileId === null || $fileId === '') {
                continue;
            }
            $fileIdsByModeleId[$modeleId][] = $fileId;
            $allFileIds[] = $fileId;
        }
        $allFileIds = array_values(array_unique($allFileIds));

        $filesById = [];
        if ($allFileIds !== []) {
            $directusFiles = $directusFilesRepository->findBy(['id' => $allFileIds]);
            foreach ($directusFiles as $file) {
                $id = $file->getId();
                if ($id !== null && $id !== '') {
                    $filesById[$id] = $file;
                }
            }
        }

        foreach ($galaxies as $galaxy) {
            $modeleId = $galaxy->getModele();
            if ($modeleId === null || $modeleId === '') {
                continue;
            }

            $orderedFiles = [];
            foreach ($fileIdsByModeleId[$modeleId] ?? [] as $fileId) {
                if (isset($filesById[$fileId])) {
                    $orderedFiles[] = $filesById[$fileId];
                }
            }

            if ($orderedFiles === []) {
                continue;
            }

            $mainImage = null;
            $thumbImages = [];
            foreach ($orderedFiles as $index => $file) {
                $filename = $file->getFilenameDisk();
                if ($filename === null || $filename === '') {
                    continue;
                }

                $sourcePath = $filename;
                if ($index === 0) {
                    try {
                        $mainImage = $cacheManager->getBrowserPath($sourcePath, 'guitar_main');
                    } catch (\Throwable) {
                        $mainImage = '/img/'.$filename;
                    }
                    continue;
                }

                try {
                    $thumbImages[] = $cacheManager->getBrowserPath($sourcePath, 'guitar_thumb');
                } catch (\Throwable) {
                    $thumbImages[] = '/img/'.$filename;
                }
            }

            if ($mainImage === null) {
                continue;
            }

            $carousel[] = [
                'title' => $galaxy->getTitle(),
                'description' => $galaxy->getDescription(),
                'files' => $orderedFiles,
                'mainImage' => $mainImage,
                'thumbImages' => $thumbImages,
            ];
        }
        
        return $this->render('carousel/index.html.twig', [
            'carousel' => $carousel
        ]);
    }
}
