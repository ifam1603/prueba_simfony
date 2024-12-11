<?php

namespace App\Entity;

use App\Repository\ProductoRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ProductoRepository::class)]
class Producto
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;  // Este será el identificador principal

    #[ORM\Column(length: 50, unique: true)]  // Asegúrate de marcarlo como único si es necesario
    private ?string $clave_producto = null;

    #[ORM\Column(length: 100)]
    private ?string $Nombre = null;

    #[ORM\Column(type: Types::DECIMAL, precision: 10, scale: 2)]
    private ?string $Precio = null;

    // Ahora se eliminará el campo redundante 'Id_producto'
    // #[ORM\Column]  // Eliminar este campo si no es necesario

    // Métodos getter y setter
    public function getId(): ?int
    {
        return $this->id;
    }

    public function getClaveProducto(): ?string
    {
        return $this->clave_producto;
    }

    public function setClaveProducto(string $clave_producto): static
    {
        $this->clave_producto = $clave_producto;
        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->Nombre;
    }

    public function setNombre(string $Nombre): static
    {
        $this->Nombre = $Nombre;
        return $this;
    }

    public function getPrecio(): ?string
    {
        return $this->Precio;
    }

    public function setPrecio(string $Precio): static
    {
        $this->Precio = $Precio;
        return $this;
    }
}
