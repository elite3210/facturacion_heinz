# -*- coding: utf-8 -*-
from odoo import models, fields, api
from datetime import datetime


class ShoeBatchGenerator(models.Model):
    _name = 'shoe.batch.generator'
    _description = 'Generador de Lotes de Calzado'
    _inherit = ['mail.thread', 'mail.activity.mixin']
    _order = 'create_date desc'

    # Campos básicos
    name = fields.Char(
        string='Número de Lote',
        required=True,
        copy=False,
        readonly=False,
        default=lambda self: self._generate_batch_number()
    )
    
    state = fields.Selection([
        ('draft', 'Borrador'),
        ('confirmed', 'Confirmado'),
        ('in_progress', 'En Producción'),
        ('done', 'Completado'),
        ('cancel', 'Cancelado')
    ], string='Estado', default='draft', tracking=True)
    
    # Información del lote
    production_date = fields.Date(
        string='Fecha de Producción',
        default=fields.Date.today,
        required=True
    )
    
    total_pairs = fields.Integer(
        string='Total de Pares',
        compute='_compute_total_pairs',
        store=True
    )
    
    # Configuración de variantes
    base_product_name = fields.Char(
        string='Producto Base',
        required=True,
        default='Calzado'
    )
    
    base_price = fields.Float(
        string='Precio Base',
        required=True,
        default=50.0
    )
    
    # Sistema de tallas personalizado
    size_input = fields.Text(
        string='Tallas y Cantidades',
        help='Ingrese las tallas separadas por comas. Ej: 34,35,35,35,36,36,36,36,37,37,37,38',
        default='33,34,34,35,35,35,36,36,36,36,37,38',
        required=True
    )
    
    # Color disponible (solo uno)
    color_selection = fields.Selection([
        ('black', 'Negro'),
        ('white', 'Blanco'),
        ('brown', 'Marrón'),
        ('blue', 'Azul'),
        ('wine', 'Vino'),
        ('gray', 'Gris'),
        ('navy', 'Azul Marino')
    ], string='Color', default='black', required=True)
    
    # Tipo de suela (solo uno)
    sole_selection = fields.Selection([
        ('rubber', 'Suela de Goma'),
        ('leather', 'Suela de Cuero'),
        ('synthetic', 'Suela Sintética'),
        ('eva', 'Suela EVA'),
        ('pu', 'Suela PU')
    ], string='Tipo de Suela', default='rubber', required=True)
    
    # Relaciones
    mrp_production_id = fields.Many2one(
        'mrp.production',
        string='Orden de Producción'
    )

    # AGREGAR ESTA LÍNEA:
    shoe_product_ids = fields.One2many(
        'shoe.product',
        'batch_id',
        string='Productos Generados'
    )
    
    # Campos calculados
    variant_count = fields.Integer(
        string='Total de Variantes',
        compute='_compute_variant_count',
        store=True
    )
    
    # Métodos de cálculo
    @api.depends('variant_count')
    def _compute_total_pairs(self):
        for record in self:
            record.total_pairs = record.variant_count
    
    @api.depends('size_input', 'color_selection', 'sole_selection')
    def _compute_variant_count(self):
        for record in self:
            # Contar las tallas desde el campo de texto
            sizes = record._get_size_list()
            
            # Como solo hay un color y una suela, el total es igual al número de tallas
            record.variant_count = len(sizes)
    
    def _get_size_list(self):
        """Obtiene la lista de tallas desde el campo de texto personalizado"""
        if not self.size_input:
            return ['36', '37', '38']  # Default
        
        # Limpiar y procesar la entrada
        try:
            size_list = [s.strip() for s in self.size_input.split(',') if s.strip()]
            return size_list
        except:
            return ['36', '37', '38']  # Fallback
    
    def _get_size_summary(self):
        """Obtiene un resumen de las tallas y cantidades"""
        sizes = self._get_size_list()
        size_count = {}
        
        for size in sizes:
            if size in size_count:
                size_count[size] += 1
            else:
                size_count[size] = 1
        
        return size_count
    
    def _get_selected_color(self):
        """Obtiene el color seleccionado"""
        return self.color_selection or 'black'
    
    def _get_selected_sole(self):
        """Obtiene el tipo de suela seleccionado"""
        return self.sole_selection or 'rubber'
    
    # Métodos de ,acción
    @api.model
    def create(self, vals):
        """Generar número de lote automáticamente si no se proporciona"""
        if not vals.get('name') or vals.get('name') == 'Nuevo':
            vals['name'] = self._generate_batch_number()
        return super(ShoeBatchGenerator, self).create(vals)
    
    def _generate_batch_number(self):
        """Genera número de lote único"""
        year = datetime.now().year
        # Buscar el último número de secuencia usado
        last_batch = self.search([('name', 'like', f'CAL-{year}-')], order='name desc', limit=1)
        if last_batch:
            try:
                last_number = int(last_batch.name.split('-')[2])
                new_number = last_number + 1
            except:
                new_number = 1
        else:
            new_number = 1
        
        return f'CAL-{year}-{str(new_number).zfill(3)}'
    
    def action_confirm(self):
        """Confirmar el lote y generar productos individuales"""
        # Generar productos automáticamente
        self._generate_individual_products()
        
        # Cambiar estado
        self.state = 'confirmed'
        return True
    
    def _generate_individual_products(self):
        """Generar productos individuales basados en la configuración del lote"""
        # Limpiar productos existentes si los hay
        self.shoe_product_ids.unlink()
        
        # Obtener lista de tallas
        sizes = self._get_size_list()
        
        # Obtener color y suela seleccionados
        color = self._get_selected_color()
        sole = self._get_selected_sole()
        
        # Obtener nombres descriptivos
        color_names = {
            'black': 'Negro', 'white': 'Blanco', 'brown': 'Marrón',
            'blue': 'Azul', 'wine': 'Vino', 'gray': 'Gris', 'navy': 'Azul Marino'
        }
        
        sole_names = {
            'rubber': 'Suela de Goma', 'leather': 'Suela de Cuero',
            'synthetic': 'Suela Sintética', 'eva': 'Suela EVA', 'pu': 'Suela PU'
        }
        
        color_name = color_names.get(color, color.title())
        sole_name = sole_names.get(sole, sole.title())
        
        # Generar productos individuales
        products_to_create = []
        sequence = 1
        
        for size in sizes:
            product_name = f"{self.base_product_name} {color_name} {sole_name} Talla {size}"
            
            product_data = {
                'batch_id': self.id,
                'name': product_name,
                'size': size,
                'color': color,
                'sole_type': sole,
                'unit_price': self.base_price,
                'cost_price': self.base_price * 0.6,  # 60% del precio como costo
                'sequence': sequence,
                'state': 'confirmed'
            }
            
            products_to_create.append(product_data)
            sequence += 1
        
        # Crear todos los productos de una vez
        if products_to_create:
            self.env['shoe.product'].create(products_to_create)
        
        # Crear todos los productos de una vez
        if products_to_create:
            created_products = self.env['shoe.product'].create(products_to_create)
            
            # Crear productos en el catálogo de Odoo
            for product in created_products:
                try:
                    product.create_catalog_product()
                except Exception as e:
                    # Log error pero continuar con otros productos
                    import logging
                    _logger = logging.getLogger(__name__)
                    _logger.warning(f"Error creando producto en catálogo: {e}")
            
    
        return True
    
    def action_create_catalog_products(self):
        """Crear todos los productos del lote en el catálogo de Odoo"""
        created_count = 0
        for product in self.shoe_product_ids:
            if not product.product_id:
                try:
                    product.create_catalog_product()
                    created_count += 1
                except Exception as e:
                    import logging
                    _logger = logging.getLogger(__name__)
                    _logger.warning(f"Error creando producto {product.sku}: {e}")
        
        return {
            'type': 'ir.actions.client',
            'tag': 'display_notification',
            'params': {
                'message': f'Se crearon {created_count} productos en el catálogo.',
                'type': 'success',
                'sticky': False,
            }
        }
    
    def action_update_catalog_products(self):
        """Actualizar todos los productos del lote en el catálogo"""
        updated_count = 0
        for product in self.shoe_product_ids:
            if product.product_id:
                try:
                    product.update_catalog_product()
                    updated_count += 1
                except Exception as e:
                    import logging
                    _logger = logging.getLogger(__name__)
                    _logger.warning(f"Error actualizando producto {product.sku}: {e}")
        
        return {
            'type': 'ir.actions.client',
            'tag': 'display_notification',
            'params': {
                'message': f'Se actualizaron {updated_count} productos en el catálogo.',
                'type': 'success',
                'sticky': False,
            }
        }

    def action_start_production(self):
        """Iniciar producción"""
        self.state = 'in_progress'
        return True
    
    def action_complete(self):
        """Completar lote"""
        self.state = 'done'
        return True
    
    def action_cancel(self):
        """Cancelar lote"""
        self.state = 'cancel'
        return True
    
    def action_reset_to_draft(self):
        """Volver a borrador"""
        self.state = 'draft'
        return True
    
    # Validaciones
    @api.constrains('size_input')
    def _check_size_input(self):
        """Validar que el campo de tallas tenga formato correcto"""
        for record in self:
            if not record.size_input:
                raise models.ValidationError('Debe ingresar al menos una talla.')
            
            try:
                sizes = [s.strip() for s in record.size_input.split(',') if s.strip()]
                if not sizes:
                    raise models.ValidationError('Debe ingresar al menos una talla válida.')
            except:
                raise models.ValidationError('Formato de tallas inválido. Use comas para separar las tallas.')
    
    @api.constrains('base_price')
    def _check_base_price(self):
        """Validar que el precio sea positivo"""
        for record in self:
            if record.base_price <= 0:
                raise models.ValidationError('El precio base debe ser mayor que cero.')
