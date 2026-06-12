<?php
use PHPUnit\Framework\TestCase;

// Simulamos la clase sin base de datos
require_once __DIR__ . '/../taller_costura/models/Encargo.php';

class EncargoTest extends TestCase
{
    private $encargo;

    protected function setUp(): void
    {
        // Pasamos null como $db porque no usaremos métodos que necesiten BD
        $this->encargo = new Encargo(null);
    }

    // Test 1: calcularSaldo devuelve la diferencia correcta
    public function test_calcularSaldo_devuelve_diferencia_correcta()
    {
        $this->encargo->monto_total = 5000;
        $this->encargo->sena = 2000;

        $resultado = $this->encargo->calcularSaldo();

        $this->assertEquals(3000, $resultado);
    }

    // Test 2: si la seña es igual al total, el saldo es 0
    public function test_calcularSaldo_cuando_senia_igual_total_es_cero()
    {
        $this->encargo->monto_total = 3000;
        $this->encargo->sena = 3000;

        $resultado = $this->encargo->calcularSaldo();

        $this->assertEquals(0, $resultado);
    }

    // Test 3: si no hay seña, el saldo es el total completo
    public function test_calcularSaldo_sin_senia_devuelve_total()
    {
        $this->encargo->monto_total = 8000;
        $this->encargo->sena = 0;

        $resultado = $this->encargo->calcularSaldo();

        $this->assertEquals(8000, $resultado);
    }
}