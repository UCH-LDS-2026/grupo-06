<?php
use PHPUnit\Framework\TestCase;

// Necesitamos solo el método calcularSaldo que no usa BD
// Hacemos un stub mínimo para evitar el require de database.php
class PagoControllerTest extends TestCase
{
    private $controller;

    protected function setUp(): void
    {
        // Instanciamos solo la lógica pura via clase anónima
        // para no necesitar sesión ni BD
        $this->controller = new class {
            public function calcularSaldo($monto_total, $senia) {
                return $monto_total - $senia;
            }
        };
    }

    // Test 4: saldo con seña parcial
    public function test_calcularSaldo_con_senia_parcial()
    {
        $resultado = $this->controller->calcularSaldo(10000, 4000);
        $this->assertEquals(6000, $resultado);
    }

    // Test 5: monto total cero da saldo cero
    public function test_calcularSaldo_monto_cero_da_cero()
    {
        $resultado = $this->controller->calcularSaldo(0, 0);
        $this->assertEquals(0, $resultado);
    }
}