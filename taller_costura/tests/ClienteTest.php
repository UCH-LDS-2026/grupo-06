<?php
use PHPUnit\Framework\TestCase;

class ClienteTest extends TestCase
{
    // ── Constructor y Getters ─────────────────────────────────

    /** @test */
    public function constructor_asigna_todos_los_atributos(): void
    {
        $cliente = new Cliente(1, 'Ana García', '2614001122', 'ana@mail.com', '2026-01-01');

        $this->assertEquals(1,              $cliente->getId());
        $this->assertEquals('Ana García',   $cliente->getNombre());
        $this->assertEquals('2614001122',   $cliente->getTelefono());
        $this->assertEquals('ana@mail.com', $cliente->getEmail());
        $this->assertEquals('2026-01-01',   $cliente->getCreatedAt());
    }

    /** @test */
    public function constructor_sin_argumentos_usa_valores_por_defecto(): void
    {
        $cliente = new Cliente();

        $this->assertEquals(0,  $cliente->getId());
        $this->assertEquals('', $cliente->getNombre());
        $this->assertEquals('', $cliente->getTelefono());
        $this->assertEquals('', $cliente->getEmail());
    }

    // ── Setters ───────────────────────────────────────────────

    /** @test */
    public function setNombre_actualiza_el_nombre(): void
    {
        $cliente = new Cliente();
        $cliente->setNombre('María López');
        $this->assertEquals('María López', $cliente->getNombre());
    }

    /** @test */
    public function setTelefono_actualiza_el_telefono(): void
    {
        $cliente = new Cliente();
        $cliente->setTelefono('2615559900');
        $this->assertEquals('2615559900', $cliente->getTelefono());
    }

    /** @test */
    public function setEmail_actualiza_el_email(): void
    {
        $cliente = new Cliente();
        $cliente->setEmail('nuevo@mail.com');
        $this->assertEquals('nuevo@mail.com', $cliente->getEmail());
    }

    // ── Casos borde ───────────────────────────────────────────

    /** @test */
    public function nombre_puede_contener_caracteres_especiales(): void
    {
        $cliente = new Cliente();
        $cliente->setNombre('Sofía Ñoño');
        $this->assertEquals('Sofía Ñoño', $cliente->getNombre());
    }

    /** @test */
    public function email_vacio_se_acepta(): void
    {
        $cliente = new Cliente(0, 'Juan', '123', '');
        $this->assertEquals('', $cliente->getEmail());
    }

    /** @test */
    public function dos_clientes_distintos_tienen_datos_independientes(): void
    {
        $c1 = new Cliente(1, 'Ana', '111', 'ana@mail.com');
        $c2 = new Cliente(2, 'Luis', '222', 'luis@mail.com');

        $this->assertNotEquals($c1->getNombre(), $c2->getNombre());
        $this->assertNotEquals($c1->getEmail(),  $c2->getEmail());
    }

    
    
}