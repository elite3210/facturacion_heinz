-- Medida: Eficiencia Global
Eficiencia Global = 
DIVIDE(
    SUM(FACT_PRODUCCION[unidades_producidas]),
    SUM(FACT_PRODUCCION[unidades_programadas]),
    0
) * 100
-- Medida: Índice de Calidad
Índice de Calidad = 
DIVIDE(
    SUM(FACT_PRODUCCION[unidades_producidas]) - SUM(FACT_PRODUCCION[defectos_encontrados]),
    SUM(FACT_PRODUCCION[unidades_producidas]),
    0
) * 100
-- Medida: Productividad por Hora
Productividad por Hora = 
DIVIDE(
    SUM(FACT_PRODUCCION[unidades_producidas]),
    SUM(FACT_PRODUCCION[tiempo_operativo]),
    0
)
-- Medida: Variación vs Objetivo
Variación vs Objetivo = 
VAR ObjetivoEficiencia = 95
VAR EficienciaActual = [Eficiencia Global]
RETURN 
IF(
    EficienciaActual >= ObjetivoEficiencia,
    "✓ Cumple",
    "⚠ No Cumple"
)
-- Medida: Trend de Producción
Trend Producción = 
VAR MesActual = SUM(FACT_PRODUCCION[unidades_producidas])
VAR MesAnterior = 
CALCULATE(
    SUM(FACT_PRODUCCION[unidades_producidas]),
    DATEADD(DIM_FECHA[fecha], -1, MONTH)
)
RETURN 
DIVIDE(MesActual - MesAnterior, MesAnterior, 0) * 100