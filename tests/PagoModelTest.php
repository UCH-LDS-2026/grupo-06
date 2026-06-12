<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../taller_costura/models/Pagos.php';

class PagoModelTest extends TestCase
{
    private $pago;

    protected function setUp(): void
    {
        // Creamos un mock de PDO que devuelve un encargo de prueba
        $encargo = [
            'id'          => 1,
            'monto_total' => '5000',
            'sena'        => '1000',
            'estado'      => 'pendiente',
            'cliente_nombre' => 'Juan'
        ];

        $stmt = $this->createMock(PDOStatement::class);
        $stmt->method('execute')->willReturn(true);
        $stmt->method('fetch')->willReturn($encargo);

        $pdo = $this->createMock(PDO::class);
        $pdo->method('prepare')->willReturn($stmt);

        $this->pago = new Pago($pdo);
    }

    // Test 6: monto negativo o cero rechazado
    public function test_registrarPago_monto_cero_devuelve_error()
    {
        $resultado = $this->pago->registrarPago(1, 1, 0);

        $this->assertFalse($resultado['ok']);
        $this->assertStringContainsString('mayor a cero', $resultado['mensaje']);
    }

    // Test 7: monto que supera el saldo pendiente es rechazado
    public function test_registrarPago_monto_mayor_al_saldo_devuelve_error()
    {
        // saldo pendiente = 5000 - 1000 = 4000, intentamos pagar 9999
        $resultado = $this->pago->registrarPago(1, 1, 9999);

        $this->assertFalse($resultado['ok']);
        $this->assertStringContainsString('saldo pendiente', $resultado['mensaje']);
    }

    // Test 8: pago válido dentro del saldo es aceptado
    public function test_registrarPago_monto_valido_devuelve_ok()
    {
        $resultado = $this->pago->registrarPago(1, 1, 500);

        $this->assertTrue($resultado['ok']);
    }
}