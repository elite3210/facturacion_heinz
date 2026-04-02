/**
 * Clase FacturaViewer
 * 
 * Maneja la visualización detallada de una factura electrónica
 */
export class FacturaViewer {
    /**
     * Constructor
     * 
     * @param {FacturaAPI} api Instancia de FacturaAPI
     * @param {string} viewerContainerId ID del contenedor del visualizador
     * @param {string} sectionId ID de la sección completa
     * @param {string} titleId ID del elemento título
     */
    constructor(api, viewerContainerId = 'factura-viewer', sectionId = 'factura-viewer-section', titleId = 'factura-title') {
        this.api = api;
        this.viewerContainer = document.getElementById(viewerContainerId);
        this.section = document.getElementById(sectionId);
        this.titleElement = document.getElementById(titleId);
        this.closeButton = document.getElementById('close-viewer-btn');
        this.currentFacturaId = null;
        this.facturaTemplate = document.getElementById('factura-template');
        this.itemTemplate = document.getElementById('item-template');

        // Configurar evento de cierre
        if (this.closeButton) {
            this.closeButton.addEventListener('click', () => this.hide());
        }
    }

    /**
     * Inicializar el visualizador
     */
    init() {
        // No es necesario hacer nada aquí
    }

    /**
     * Cargar y mostrar una factura
     * 
     * @param {number} id ID de la factura a mostrar
     */
    async loadFactura(id) {
        if (!this.viewerContainer || !this.section) return;

        try {
            this.currentFacturaId = Number(id);
            this.showLoading();

            const result = await this.api.getFacturaById(id);

            if (!result.success) {
                throw new Error(result.message || 'Error al cargar la factura');
            }
            
            this.renderFactura(result.data);
            this.show();
        } catch (error) {
            this.showError(error.message);
            console.log('Error en FacturaViewer:', error);
            
        }
    }

    /**
     * Renderizar factura
     * 
     * @param {Object} data Datos de la factura
     */
    renderFactura(data) {


        if (!this.viewerContainer || !this.facturaTemplate) return;

        // Limpiar contenedor
        this.viewerContainer.innerHTML = '';

        // Verificar si tenemos los detalles necesarios
        if (!data.detalles) {
            this.showError('La factura no contiene datos válidos');
            return;
        }

        console.log('data invoice:', data);
        console.log('data invoice detalles:', data.detalles);

        // Establecer título
        if (this.titleElement) {
            this.titleElement.textContent = data.detalles.factura.serie;
        }

        // Clonar la plantilla
        const facturaElement = this.facturaTemplate.content.cloneNode(true);

        // Rellenar datos del emisor
        facturaElement.querySelector('.empresa-nombre').textContent = data.detalles.emisor.razonSocial;
        facturaElement.querySelector('.direccion-emisor').textContent = data.detalles.emisor.direccion;
        facturaElement.querySelector('.distrito-emisor').textContent = data.detalles.emisor.distrito;
        facturaElement.querySelector('.provincia-emisor').textContent = data.detalles.emisor.provincia;
        facturaElement.querySelector('.departamento-emisor').textContent = data.detalles.emisor.departamento;
        facturaElement.querySelector('.ruc-emisor').textContent = data.detalles.emisor.ruc;

        // Rellenar datos de la factura
        facturaElement.querySelector('.serie-numero').textContent = data.detalles.factura.serie;
        facturaElement.querySelector('.fecha-emision').textContent = this.formatDate(data.detalles.factura.fechaEmision);
        facturaElement.querySelector('.cliente-nombre').textContent = data.detalles.receptor.razonSocial;
        facturaElement.querySelector('.cliente-ruc').textContent = data.detalles.receptor.ruc;
        facturaElement.querySelector('.cliente-direccion').textContent = data.detalles.receptor.direccion;
        facturaElement.querySelector('.moneda').textContent = this.getMonedaText(data.detalles.factura.moneda);
        facturaElement.querySelector('.forma-pago').textContent = data.detalles.factura.formaPago;
        facturaElement.querySelector('.guia-remision').textContent = data.detalles.factura.guiaRemision ? data.detalles.factura.guiaRemision: 'N/D';

        // Rellenar detalles/items
        const itemsContainer = facturaElement.querySelector('.items-container');
        if (itemsContainer && data.detalles.detalles) {
            this.renderItems(itemsContainer, data.detalles.detalles);
        }

        // Rellenar totales
        facturaElement.querySelector('.monto-letras').textContent = data.detalles.factura.totalLetras;
        facturaElement.querySelector('.subtotal').textContent = this.formatMoney(data.detalles.totales.gravadas,data.detalles.factura.moneda);
        facturaElement.querySelector('.valor-venta').textContent = this.formatMoney(data.detalles.totales.gravadas,data.detalles.factura.moneda);
        facturaElement.querySelector('.igv').textContent = this.formatMoney(data.detalles.totales.igv,data.detalles.factura.moneda);
        facturaElement.querySelector('.total').textContent = this.formatMoney(data.detalles.totales.total,data.detalles.factura.moneda);

        // Generar QR code
        const qrCodeElement = facturaElement.querySelector('#qrcode');
        if (qrCodeElement) {
            setTimeout(() => {
                this.generateQRCode(qrCodeElement, data.detalles.emisor.ruc, data.detalles.receptor.ruc, data.detalles.factura.serie);
            }, 100); // Pequeño retraso para asegurar que el elemento esté en el DOM
        }

        // Agregar al contenedor
        this.viewerContainer.appendChild(facturaElement);
    }

    /**
     * Renderizar items de la factura
     * 
     * @param {HTMLElement} container Contenedor donde insertar los items
     * @param {Array} items Lista de items a mostrar
     */
    renderItems(container, items) {
        if (!container || !this.itemTemplate || !items || !items.length) return;

        // Limpiar contenedor
        container.innerHTML = '';

        items.forEach(item => {
            const itemElement = this.itemTemplate.content.cloneNode(true);

            itemElement.querySelector('.item-cantidad').textContent = item.cantidad;
            itemElement.querySelector('.item-unidad').textContent = this.getUnidadMedidaText(item.unidad);
            itemElement.querySelector('.item-descripcion').textContent = item.descripcion;
            itemElement.querySelector('.item-valor').textContent = this.formatMoney(item.valorUnitario, false);
            itemElement.querySelector('.item-importe').textContent = this.formatMoney(item.valorUnitario * item.cantidad, false);

            container.appendChild(itemElement);
        });
    }

    /**
     * Generar código QR
     * 
     * @param {HTMLElement} container Elemento donde generar el QR
     * @param {string} rucEmisor RUC del emisor
     * @param {string} rucReceptor RUC del receptor
     * @param {string} serieNumero Serie y número de la factura
     */
    async generateQRCode(container, rucEmisor, rucReceptor, serieNumero) {
        if (!container || !window.QRCode) return;

        // Limpiar contenedor primero
        container.innerHTML = '';

        // Construir datos para el QR (según formato SUNAT)
        const qrData = `${rucEmisor}|${serieNumero}|${rucReceptor}`;

        const qrCanvas = document.createElement('canvas');
        //const qrData = "https://www.heinzsport.com"; // Reemplaza con el enlace que quieras codificar
        await QRCode.toCanvas(qrCanvas, qrData, { errorCorrectionLevel: 'H' });  // Esperar a que se genere el QR
        const qrDataURL = qrCanvas.toDataURL('image/png');//code64
        //container.appendChild(qrDataURL);
        // Crear QR
        //new QRCode(container, {
        // text: qrData,
        //  width: 128,
        //  height: 128,
        //  colorDark: "#000000",
        //   colorLight: "#ffffff",
        //correctLevel: QRCode.CorrectLevel.H
        //});
    }

    generateQRCode_revison(container, rucEmisor, rucReceptor, serieNumero) {
        if (!container || !window.QRCode) return;

        // Limpiar contenedor primero
        container.innerHTML = '';

        // Construir datos para el QR (según formato SUNAT)
        const qrData = `${rucEmisor}|${serieNumero}|${rucReceptor}`;

        // Crear QR
        new QRCode(container, {
            text: qrData,
            width: 128,
            height: 128,
            colorDark: "#000000",
            colorLight: "#ffffff",
            //correctLevel: QRCode.CorrectLevel.H
        });
    }

    /**
     * Formatear fecha para visualización
     * 
     * @param {string} dateString Fecha en formato ISO
     * @returns {string} Fecha formateada
     */
    formatDate(dateString) {
        if (!dateString) return '';
        return  dateString.slice(8, 10) + "-" + dateString.slice(5, 7) + "-" + dateString.slice(0, 4);
    }

    /**
     * Formatear valor monetario
     * 
     * @param {number|string} value Valor a formatear
     * @param {boolean} codigo_moneda Incluir símbolo ISO de moneda
     * @returns {string} Valor formateado
     */
    formatMoney(numValue, codigo_moneda) {
        let formatted = '';
        
        if (codigo_moneda === 'PEN') {
            formatted = new Intl.NumberFormat('es-PE', {style: 'currency',currency: 'PEN'}).format(numValue);
        } else {
            formatted = new Intl.NumberFormat('en-US', {style: 'currency',currency: 'USD'}).format(numValue);
        }
        return formatted;
    }

    /**
     * Obtener texto descriptivo para la moneda
     * 
     * @param {string} monedaCode Código de moneda
     * @returns {string} Texto descriptivo
     */
    getMonedaText(monedaCode) {
        const monedas = {
            'PEN': 'SOLES',
            'USD': 'DÓLARES AMERICANOS',
            'EUR': 'EUROS'
        };

        return monedas[monedaCode] || monedaCode;
    }

    /**
     * Obtener texto descriptivo para unidad de medida
     * 
     * @param {string} unidadCode Código de unidad
     * @returns {string} Texto descriptivo
     */
    getUnidadMedidaText(unidadCode) {
        const unidades = {
            'NIU': 'UNIDAD',
            'ZZ': 'SERVICIO',
            'KGM': 'KILOGRAMO',
            'LTR': 'LITRO',
            'MTR': 'METRO',
            'MTK': 'METRO CUADRADO',
            'MTQ': 'METRO CÚBICO',
            'GLL': 'GALÓN',
            'HUR': 'HORA',
            'DAY': 'DÍA'
        };

        return unidades[unidadCode] || unidadCode;
    }

    /**
     * Mostrar el visualizador
     */
    show() {
        if (this.section) {
            this.section.classList.remove('d-none');
            // Desplazar a la sección
            this.section.scrollIntoView({ behavior: 'smooth' });
        }
    }

    /**
     * Ocultar el visualizador
     */
    hide() {
        if (this.section) {
            this.section.classList.add('d-none');
        }
        this.currentFacturaId = null;
    }

    /**
     * Mostrar indicador de carga
     */
    showLoading() {
        if (!this.viewerContainer) return;

        this.viewerContainer.innerHTML = `
            <div class="text-center py-5">
                <div class="loader"></div>
                <p class="mt-3">Cargando factura...</p>
            </div>
        `;

        this.show();
    }

    /**
     * Mostrar mensaje de error
     * 
     * @param {string} message Mensaje de error
     */
    showError(message) {
        if (!this.viewerContainer) return;

        this.viewerContainer.innerHTML = `
            <div class="alert alert-danger" role="alert">
                <h4 class="alert-heading">Error</h4>
                <p>${message}</p>
            </div>
        `;
    }

    /**
     * Obtener datos de la factura actual
     * 
     * @returns {number|null} ID de la factura actual
     */
    getCurrentFacturaId() {
        return this.currentFacturaId;
    }
}