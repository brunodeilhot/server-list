<?php

namespace App\Controller;

use App\Form\UploadFormType;
use App\Service\UploadService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class UploadController extends AbstractController
{
    #[Route('/upload', name: 'app_upload')]
    public function index(Request $request, UploadService $uploadService): Response
    {
        $form = $this->createForm(UploadFormType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $uploadedFile = $form['upload_file']->getData();

            if ($uploadedFile) {
                try {
                    $uploadService->saveFile($uploadedFile);

                    $this->addFlash('success', 'Your file has been uploaded successfully');

                    return $this->redirectToRoute('app_server_list');
                } catch (\Exception) {
                    $this->addFlash('error', 'An error occurred while uploading the file');
                    return $this->render('upload/index.html.twig', [
                        'form' => $form->createView(),
                    ]);
                }
            }
            $this->addFlash('error', 'No file was uploaded');
        }

        return $this->render('upload/index.html.twig', [
            'form' => $form->createView(),
        ]);
    }
}
