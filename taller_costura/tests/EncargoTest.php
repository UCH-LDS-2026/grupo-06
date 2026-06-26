<?php
use PHPUnit\Framework\TestCase;

// Cargamos solo la clase Encargo sin la BD
// Para eso creamos un stub del constructor que no necesita conexión real
class EncargoTest extends TestCase
{
    private function makeEncargo(float $montoTotal, float $sena, string $fechaEntrega = ''): object
    {
        // Creamos un mock parcial de Encargo sin pasar PDO real
        $encargo = $this->getMockBuilder(Encargo::class)
                        ->disableOriginalConstructor()
                        ->onlyMethods([]) // no mockeamos ningún método, usamos los reales
                        ->getMock();

        $encargo->monto_total   = $montoTotal;
        $encargo->sena          = $sena;
        $encargo->fecha_entrega = $fechaEntrega ?: date('Y-m-d', strtotime('+5 days'));

        return $encargo;
    }

    // ── calcularSaldo() ───────────────────────────────────────

    /** @test */
    public function calcularSaldo_devuelve_diferencia_correcta(): void
    {
        $encargo = $this->makeEncargo(10000, 3000);
        $this->assertEquals(7000, $encargo->calcularSaldo());
    }

    /** @test */
    public function calcularSaldo_con_sena_cero_devuelve_total(): void
    {
        $encargo = $this->makeEncargo(5000, 0);
        $this->assertEquals(5000, $encargo->calcularSaldo());
    }

    /** @test */
    public function calcularSaldo_pagado_total_devuelve_cero(): void
    {
        $encargo = $this->makeEncargo(8000, 8000);
        $this->assertEquals(0, $encargo->calcularSaldo());
    }

    /** @test */
    public function calcularSaldo_con_decimales_es_correcto(): void
    {
        $encargo = $this->makeEncargo(1500.50, 500.25);
        $this->assertEquals(1000.25, $encargo->calcularSaldo());
    }

    // ── calcularDemora() ──────────────────────────────────────

    /** @test */
    public function calcularDemora_fecha_futura_devuelve_dias_positivos(): void
    {
        $encargo = $this->makeEncargo(5000, 0, date('Y-m-d', strtotime('+10 days')));
        $dias = $encargo->calcularDemora();
        $this->assertGreaterThan(0, $dias);
    }

    /** @test */
    public function calcularDemora_fecha_hoy_devuelve_cero(): void
    {
        $encargo = $this->makeEncargo(5000, 0, date('Y-m-d'));
        $this->assertEquals(0, $encargo->calcularDemora());
    }

    /** @test */
    public function calcularDemora_fecha_pasada_devuelve_dias_vencidos(): void
    {
        $encargo = $this->makeEncargo(5000, 0, date('Y-m-d', strtotime('-3 days')));
        $this->assertEquals(3, $encargo->calcularDemora());
    }


    // ── Manejo de errores / casos borde ──────────────────────

    /** @test */
    public function calcularSaldo_sena_mayor_al_total_devuelve_negativo(): void
    {
        $encargo = $this->makeEncargo(5000, 6000);
        $this->assertLessThan(0, $encargo->calcularSaldo());
    }

    /** @test */
    public function calcularSaldo_monto_total_cero_devuelve_cero(): void
    {
        $encargo = $this->makeEncargo(0, 0);
        $this->assertEquals(0, $encargo->calcularSaldo());
    }

    /** @test */
    public function calcularDemora_fecha_muy_lejana_devuelve_dias_grandes(): void
    {
        $encargo = $this->makeEncargo(5000, 0, date('Y-m-d', strtotime('+365 days')));
        $this->assertGreaterThan(300, $encargo->calcularDemora());
    }

}