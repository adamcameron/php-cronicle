<?php

namespace App\Controller;

use App\Entity\DynamicTaskMessage;
use App\Form\DynamicTaskMessageType;
use App\Message\ScheduleReloadMessage;
use App\Message\TaskMessage;
use App\Repository\DynamicTaskMessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Messenger\MessageBusInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/dynamic-task')]
class DynamicTaskController extends AbstractController
{
    #[Route('/', name: 'dynamic_task_index', methods: ['GET'])]
    public function index(DynamicTaskMessageRepository $repository): Response
    {
        $tasks = $repository->findBy([], ['name' => 'ASC']);

        return $this->render('dynamic_task/index.html.twig', [
            'tasks' => $tasks,
        ]);
    }

    #[Route('/new', name: 'dynamic_task_new', methods: ['GET', 'POST'])]
    public function new(Request $request, EntityManagerInterface $entityManager, MessageBusInterface $bus): Response
    {
        $task = new DynamicTaskMessage();
        $form = $this->createForm(DynamicTaskMessageType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->persist($task);
            $entityManager->flush();
            $bus->dispatch(new ScheduleReloadMessage());

            $this->addFlash('success', 'Task "' . $task->getName() . '" created successfully!');

            return $this->redirectToRoute('dynamic_task_index');
        }

        return $this->render('dynamic_task/edit.html.twig', [
            'task' => $task,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'dynamic_task_edit', methods: ['GET'])]
    public function showEditTaskForm(DynamicTaskMessage $task): Response
    {
        $form = $this->createForm(DynamicTaskMessageType::class, $task);

        return $this->render('dynamic_task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    #[Route('/{id}/edit', name: 'dynamic_task_edit_post', methods: ['POST'])]
    public function processEditTask(Request $request, DynamicTaskMessage $task, EntityManagerInterface $entityManager, MessageBusInterface $bus): Response
    {
        $form = $this->createForm(DynamicTaskMessageType::class, $task);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();
            $bus->dispatch(new ScheduleReloadMessage());

            $this->addFlash('success', 'Task "' . $task->getName() . '" updated successfully!');

            return $this->redirectToRoute('dynamic_task_index');
        }

        return $this->render('dynamic_task/edit.html.twig', [
            'task' => $task,
            'form' => $form->createView(),
        ]);
    }

    /** @codeCoverageIgnore */
    #[Route('/{id}/run', name: 'dynamic_task_run', methods: ['GET'])]
    public function runNow(DynamicTaskMessage $task, MessageBusInterface $bus, LoggerInterface $tasksLogger): Response
    {
        $tasksLogger->info('Task manually executed via UI', [
            'task_id' => $task->getId(),
            'task_type' => $task->getType(),
            'task_name' => $task->getName()
        ]);

        $bus->dispatch(new TaskMessage($task));

        $this->addFlash('success', 'Task "' . $task->getName() . '" queued for immediate execution');

        return $this->redirectToRoute('dynamic_task_index');
    }

    /** @codeCoverageIgnore */
    #[Route('/{id}/delete', name: 'dynamic_task_delete', methods: ['GET'])]
    public function delete(DynamicTaskMessage $task, EntityManagerInterface $entityManager, MessageBusInterface $bus, LoggerInterface $tasksLogger): Response
    {
        $tasksLogger->info('Task deleted via UI', [
            'task_id' => $task->getId(),
            'task_type' => $task->getType(),
            'task_name' => $task->getName()
        ]);

        $entityManager->remove($task);
        $entityManager->flush();
        $bus->dispatch(new ScheduleReloadMessage());

        $this->addFlash('success', 'Task "' . $task->getName() . '" deleted successfully!');

        return $this->redirectToRoute('dynamic_task_index');
    }
}
