# Sistema de Inventario de Prendas de Vestir
# Versión básica usando conceptos fundamentales de Python
# Para curso básico de Python

# Variables globales para almacenar los datos
inventario = {}  # Diccionario principal
lista_registros = []  # Lista para mantener orden de ingreso
contador = 0  # Contador de registros

# Tupla con categorías válidas
categorias = ("camisas", "pantalones", "vestidos", "faldas", "chaquetas", 
              "zapatos", "accesorios", "ropa_interior", "otras")

# Función para mostrar el menú (Estructura Secuencial)
def mostrar_menu():
    print("\n" + "="*50)
    print("   INVENTARIO DE PRENDAS DE VESTIR")
    print("="*50)
    print("1. Agregar prenda")
    print("2. Ver inventario completo")
    print("3. Reporte por categoría")
    print("4. Salir")
    print("-"*50)

# Función para mostrar categorías disponibles
def mostrar_categorias():
    print("\nCategorías disponibles:")
    for i in range(len(categorias)):
        print(f"{i+1}. {categorias[i].title()}")

# Función para validar la categoría ingresada (Estructura Selectiva)
def validar_categoria(cat):
    cat = cat.lower()
    if cat in categorias:
        return cat
    else:
        return None

# Función para validar números positivos (Estructura Selectiva)
def validar_numero(valor, tipo):
    if tipo == "entero":
        if valor.isdigit() and int(valor) > 0:
            return int(valor)
        else:
            return None
    elif tipo == "decimal":
        try:
            num = float(valor)
            if num > 0:
                return num
            else:
                return None
        except:
            return None

# Función para agregar prenda (Combina las 3 estructuras)
def agregar_prenda():
    global contador
    
    print("\n--- AGREGAR NUEVA PRENDA ---")
    
    # Estructura Repetitiva - Validar categoría
    while True:
        mostrar_categorias()
        cat_input = input("\nIngrese la categoría: ")
        categoria = validar_categoria(cat_input)
        
        # Estructura Selectiva
        if categoria != None:
            break
        else:
            print("❌ Categoría no válida. Intente de nuevo.")
    
    # Estructura Repetitiva - Validar cantidad
    while True:
        cant_input = input("Ingrese la cantidad: ")
        cantidad = validar_numero(cant_input, "entero")
        
        # Estructura Selectiva
        if cantidad != None:
            break
        else:
            print("❌ Cantidad debe ser un número entero positivo.")
    
    # Estructura Repetitiva - Validar precio
    while True:
        precio_input = input("Ingrese el precio unitario: $")
        precio = validar_numero(precio_input, "decimal")
        
        # Estructura Selectiva
        if precio != None:
            break
        else:
            print("❌ Precio debe ser un número positivo.")
    
    # Calcular valor total
    valor_total = cantidad * precio
    
    # Crear tupla con información del item
    item = (cantidad, precio, valor_total)
    
    # Estructura Selectiva - Agregar al diccionario
    if categoria in inventario:
        inventario[categoria].append(item)
    else:
        inventario[categoria] = [item]
    
    # Agregar a lista de registros
    registro = (categoria, cantidad, precio, valor_total)
    lista_registros.append(registro)
    contador += 1
    
    print(f"\n✅ Prenda agregada exitosamente!")
    print(f"Categoría: {categoria.title()}")
    print(f"Cantidad: {cantidad}")
    print(f"Precio: ${precio:.2f}")
    print(f"Total: ${valor_total:.2f}")

# Función para mostrar inventario completo (Estructura Secuencial y Repetitiva)
def mostrar_inventario():
    print("\n--- INVENTARIO COMPLETO ---")
    
    # Estructura Selectiva
    if len(lista_registros) == 0:
        print("❌ No hay registros en el inventario.")
        return
    
    print(f"{'#':<3} {'Categoría':<15} {'Cantidad':<8} {'Precio':<10} {'Total':<10}")
    print("-"*50)
    
    total_inventario = 0
    
    # Estructura Repetitiva
    for i in range(len(lista_registros)):
        categoria, cantidad, precio, total = lista_registros[i]
        print(f"{i+1:<3} {categoria.title():<15} {cantidad:<8} ${precio:<9.2f} ${total:<9.2f}")
        total_inventario += total
    
    print("-"*50)
    print(f"TOTAL DEL INVENTARIO: ${total_inventario:.2f}")
    print(f"REGISTROS TOTALES: {contador}")

# Función para generar reporte por categoría (Estructura Repetitiva)
def reporte_categoria():
    print("\n--- REPORTE POR CATEGORÍA ---")
    
    # Estructura Selectiva
    if len(inventario) == 0:
        print("❌ No hay registros en el inventario.")
        return
    
    total_general = 0
    
    # Estructura Repetitiva - Recorrer categorías
    for categoria in inventario:
        print(f"\n📦 CATEGORÍA: {categoria.upper()}")
        print("-"*40)
        
        items = inventario[categoria]
        total_categoria = 0
        cantidad_categoria = 0
        
        # Estructura Repetitiva - Recorrer items de la categoría
        for i in range(len(items)):
            cantidad, precio, valor = items[i]
            print(f"  Item {i+1}: {cantidad} unidades × ${precio:.2f} = ${valor:.2f}")
            total_categoria += valor
            cantidad_categoria += cantidad
        
        print(f"  SUBTOTAL: {cantidad_categoria} unidades - ${total_categoria:.2f}")
        total_general += total_categoria
    
    print(f"\n{'='*40}")
    print(f"TOTAL GENERAL: ${total_general:.2f}")

# Función principal del programa (Estructura Repetitiva principal)
def main():
    print("🚀 Iniciando Sistema de Inventario...")
    
    # Estructura Repetitiva principal
    while True:
        mostrar_menu()
        opcion = input("Seleccione una opción (1-4): ")
        
        # Estructura Selectiva para manejar opciones
        if opcion == "1":
            agregar_prenda()
            input("\nPresione Enter para continuar...")
        elif opcion == "2":
            mostrar_inventario()
            input("\nPresione Enter para continuar...")
        elif opcion == "3":
            reporte_categoria()
            input("\nPresione Enter para continuar...")
        elif opcion == "4":
            print("\n¡Gracias por usar el sistema!")
            print("👋 ¡Hasta luego!")
            break
        else:
            print("❌ Opción no válida. Seleccione 1, 2, 3 o 4.")
            input("Presione Enter para continuar...")

# Ejecutar el programa
if __name__ == "__main__":
    main()