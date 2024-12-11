<?php

namespace App\Controller;

use App\Entity\Producto;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Form\ProductoType; // Agregar la importación de ProductoType
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\StreamedResponse;


class ProductoController extends AbstractController
{
    #[Route('/', name: 'producto_index', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $productos = $em->getRepository(Producto::class)->findAll();
        return $this->render('producto/index.html.twig', [
            'productos' => $productos,
        ]);
    }

    #[Route('/producto/nuevo', name: 'producto_nuevo', methods: ['GET', 'POST'])]
    public function nuevo(Request $request, EntityManagerInterface $entityManager): Response
    {
        $producto = new Producto();
        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                // Llamar al procedimiento almacenado
                $connection = $entityManager->getConnection();
                $query = 'CALL InsertarProducto(:clave_producto, :nombre, :precio)';
                $stmt = $connection->prepare($query);
                $stmt->executeQuery([
                    'clave_producto' => $producto->getClaveProducto(),
                    'nombre' => $producto->getNombre(),
                    'precio' => $producto->getPrecio(),
                ]);
            
                $this->addFlash('success', 'Producto insertado correctamente.');
                return $this->redirectToRoute('producto_index');
            } catch (\Doctrine\DBAL\Exception $e) {
                // Verificar si es el error del procedimiento almacenado
                if (str_contains($e->getMessage(), 'La clave del producto ya existe')) {
                    $this->addFlash('danger', 'La clave del producto ya existe.');
                } else {
                    // Otros errores
                    $this->addFlash('danger', 'Ocurrió un error al insertar el producto.');
                }
            }
        }
    
        return $this->render('producto/nuevo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/producto/editar/{id}', name: 'producto_editar', methods: ['GET', 'POST'])]
    public function editar(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $producto = $entityManager->getRepository(Producto::class)->find($id);

        if (!$producto) {
            throw $this->createNotFoundException('El producto no existe.');
        }

        $form = $this->createForm(ProductoType::class, $producto);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager->flush();

            return $this->redirectToRoute('producto_index');
        }

        return $this->render('producto/editar.html.twig', [
            'form' => $form->createView(),
            'producto' => $producto,
        ]);
    }

    #[Route('/producto/borrar/{id}', name: 'producto_borrar', methods: ['POST'])]
    public function borrar(int $id, Request $request, EntityManagerInterface $entityManager): Response
    {
        $producto = $entityManager->getRepository(Producto::class)->find($id);

        if (!$producto) {
            throw $this->createNotFoundException('El producto no existe.');
        }

        if ($this->isCsrfTokenValid('borrar' . $producto->getId(), $request->request->get('_token'))) {
            $entityManager->remove($producto);
            $entityManager->flush();

            $this->addFlash('success', 'Producto eliminado correctamente.');
        }

        return $this->redirectToRoute('producto_index');
    }

    #[Route('/producto/exportar', name: 'producto_exportar', methods: ['GET'])]
    public function exportar(EntityManagerInterface $entityManager): Response
    {
        $productos = $entityManager->getRepository(Producto::class)->findAll();

        // Crear un nuevo Spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        // Configurar encabezados
        $sheet->setCellValue('A1', 'ID');
        $sheet->setCellValue('B1', 'Clave');
        $sheet->setCellValue('C1', 'Nombre');
        $sheet->setCellValue('D1', 'Precio');

        // Rellenar datos
        $row = 2; // Empezar después de los encabezados
        foreach ($productos as $producto) {
            $sheet->setCellValue('A' . $row, $producto->getId());
            $sheet->setCellValue('B' . $row, $producto->getClaveProducto());
            $sheet->setCellValue('C' . $row, $producto->getNombre());
            $sheet->setCellValue('D' . $row, $producto->getPrecio());
            $row++;
        }

        // Generar el archivo Excel
        $response = new StreamedResponse(function () use ($spreadsheet) {
            $writer = new Xlsx($spreadsheet);
            $writer->save('php://output');
        });

        // Configurar cabeceras HTTP
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->headers->set('Content-Disposition', 'attachment;filename="productos.xlsx"');
        $response->headers->set('Cache-Control', 'max-age=0');

        return $response;
    }
}
