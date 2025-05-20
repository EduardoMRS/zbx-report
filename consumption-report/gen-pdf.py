import os
import sys
import json
import matplotlib.pyplot as plt
from datetime import datetime, timedelta
from fpdf import FPDF
from PyPDF2 import PdfReader, PdfWriter

class PDFGenerator:
    def __init__(self):
        self.temp_dir = os.path.join(os.path.dirname(__file__), 'temp')
        os.makedirs(self.temp_dir, exist_ok=True)
        
        # Configurações de layout
        self.page_width = 210  # A4 em mm (largura)
        self.page_height = 297  # A4 em mm (altura)
        self.margins = {
            'left': 20,
            'right': 20,
            'top': 30,
            'bottom': 20
        }
        self.content_width = self.page_width - self.margins['left'] - self.margins['right']
    
    def calculate_date_range(self, values):
        """Calcula o período com base no primeiro e último timestamp do histórico"""
        if not values:
            now = datetime.now()
            return now, now  # Fallback
        
        timestamps = [int(item['timestamp']) for item in values]
        return datetime.fromtimestamp(min(timestamps)), datetime.fromtimestamp(max(timestamps))
        
    
    def format_bandwidth(self, value_bps):
        """Formata o valor de banda dinamicamente"""
        value = float(value_bps)
        if value >= 10**9:  # Gbps
            return f"{value/10**9:.2f} Gbps"
        elif value >= 10**6:  # Mbps
            return f"{value/10**6:.2f} Mbps"
        elif value >= 10**3:  # Kbps
            return f"{value/10**3:.2f} Kbps"
        else:
            return f"{value:.2f} bps"
    
    def generate_wave_graph(self, values, output_path):
        timestamps = [int(item['timestamp']) for item in values]
        numeric_values = [int(item['value']) for item in values]
        
        dates = [datetime.fromtimestamp(ts) for ts in timestamps]
        
        plt.figure(figsize=(10, 5))
        plt.plot(dates, numeric_values, linestyle='-', linewidth=2, color='#1f77b4')
        
        plt.gca().set_facecolor('#f5f5f5')
        plt.grid(True, linestyle='--', alpha=0.5)
        
        max_value = max(numeric_values) if numeric_values else 0
        y_label = self.format_bandwidth(max_value)
        plt.ylabel(f'Consumo ({y_label})', fontsize=12)
        
        plt.xticks(rotation=45)
        plt.tight_layout()
        
        plt.savefig(output_path, dpi=300, bbox_inches='tight', facecolor='#f5f5f5')
        plt.close()
        
    def format_bandwidth_total(self, value_bits):
        """Formata o valor total de banda em unidades de volume (GB, MB, KB)"""
        value = float(value_bits)
        if value >= 8 * 1024**4:  # GB (8 bits = 1 byte, 1024^4 = TB)
            return f"{(value/8)/1024**4:.2f} TB"
        elif value >= 8 * 1024**3:  # GB
            return f"{(value/8)/1024**3:.2f} GB"
        elif value >= 8 * 1024**2:  # MB
            return f"{(value/8)/1024**2:.2f} MB"
        elif value >= 8 * 1024:  # KB
            return f"{(value/8)/1024:.2f} KB"
        else:
            return f"{value/8:.2f} B"
    
    def calculate_stats(self, values, date_start, date_end, period):
        if not values:
            return {
                'Consumo Máximo': "0 bps",
                'Consumo Total': "0 B",
                'Período Analisado': "Nenhum dado disponível"
            }
        
        numeric_values = [int(item['value']) for item in values]
        
        # Restante do cálculo permanece o mesmo
        max_value = max(numeric_values) if numeric_values else 0
        speed_unit = self.format_bandwidth(max_value).split()[-1]
        
        def convert_speed(value):
            if speed_unit == 'Gbps':
                return value / 10**9
            elif speed_unit == 'Mbps':
                return value / 10**6
            elif speed_unit == 'Kbps':
                return value / 10**3
            return value
        
        total_bits = sum(numeric_values) * 300  # Assumindo intervalo de 5 minutos (300 segundos)
        
        return {
            'Consumo Máximo': f"{convert_speed(max(numeric_values)):.2f} {speed_unit}",
            'Consumo Total': self.format_bandwidth_total(total_bits),
            'Período Analisado': f"{date_start} a {date_end}({period} dias)"
        }
    
    def apply_timbrado(self, content_pdf_path, output_path):
        """Combina o PDF gerado com o timbrado como background em todas as páginas"""
        try:
            # Ler o PDF de conteúdo
            content_pdf = PdfReader(content_pdf_path)
            
            # Ler o PDF do timbrado (apenas a primeira página)
            timbrado_path = os.path.join(os.path.dirname(os.path.abspath(__file__)), 'timbrado.pdf')
            if not os.path.exists(timbrado_path):
                raise FileNotFoundError(f"Arquivo timbrado.pdf não encontrado em: {timbrado_path}")
            
            timbrado_pdf = PdfReader(timbrado_path)
            timbrado_page = timbrado_pdf.pages[0]
            
            # Criar um novo PDF combinando os dois
            output_pdf = PdfWriter()
            
            for page in content_pdf.pages:
                # Mesclar o timbrado como background
                page.merge_page(timbrado_page)
                output_pdf.add_page(page)
            
            # Salvar o resultado final
            with open(output_path, 'wb') as fout:
                output_pdf.write(fout)
                
        except Exception as e:
            print(f"Erro ao aplicar timbrado: {str(e)}")
            raise
    
    def generate_pdf(self, json_data, output_path):
        try:
            if isinstance(json_data, str):
                json_data = json.loads(json_data)
            
            responsavel_tecnico = json_data['responsavel']
            data = json_data['data']
            
            # Calcular período com base no histórico (fallback)
            start_date, end_date = self.calculate_date_range(data['history']['values'])
            
            # Usar valores fornecidos ou calcular automaticamente
            date_start = json_data.get('date_start', start_date.strftime('%d/%m/%Y'))
            date_end = json_data.get('date_end', end_date.strftime('%d/%m/%Y'))
            period_days = json_data.get('period', (end_date - start_date).days)
            
            # Formatar string do período
            period_str = f"{date_start} a {date_end}"
            
            stats = self.calculate_stats(data['history']['values'], date_start, date_end, period_days)
            
            # Gerar gráfico
            graph_filename = f"{data['name'].replace(' ', '_')[:20]}_graph.png"
            graph_path = os.path.join(self.temp_dir, graph_filename)
            self.generate_wave_graph(data['history']['values'], graph_path)
            
            # Criar PDF temporário
            temp_pdf_path = os.path.join(self.temp_dir, f"{data['name'].replace(' ', '_')[:20]}temp_content.pdf")
            
            pdf = FPDF(orientation='P', unit='mm', format='A4')
            pdf.set_auto_page_break(auto=True, margin=self.margins['bottom'])
            pdf.add_page()
            
            # Configurar margens
            pdf.set_left_margin(self.margins['left'])
            pdf.set_right_margin(self.margins['right'])
            pdf.set_y(self.margins['top'])
            
            # Título do relatório
            pdf.set_font('Arial', 'B', 8)
            pdf.cell(0, 10, 'RELATÓRIO TÉCNICO DE FORNECIMENTO DE LINK', 0, 1, 'C')
            pdf.ln(5)
            
            # Configurações de fonte
            pdf.set_font('Arial', 'B', 14)
            
            # Cabeçalho
            pdf.cell(0, 10, data['name'], 0, 1, 'C')
            
            # Informações do relatório
            pdf.set_font('Arial', '', 10)
            pdf.multi_cell(0, 10, f'ASSUNTO: Consumo referente a {period_str}', 0, 1)
            pdf.ln(5)
            
            pdf.cell(0, 10, f'Data de emissão: {datetime.now().strftime("%d/%m/%Y")}', 0, 1)
            
            
            # Tabela de estatísticas
            pdf.set_font('Arial', 'B', 10)
            pdf.cell(0, 10, 'Estatísticas:', 0, 1)
            pdf.set_font('Arial', '', 8)

            # Ajuste das larguras das colunas
            col_widths = [60, 50]  # Nome do campo e Valor/Unidade combinados

            for key, value in stats.items():
                if pdf.get_y() + 10 > self.page_height - self.margins['bottom']:
                    pdf.add_page()
                    pdf.set_y(self.margins['top'])
                
                pdf.cell(col_widths[0], 8, key, 1, 0, 'L')
                pdf.cell(col_widths[1], 8, value, 1, 1, 'C')
            
            pdf.ln(15)
            
            # Gráfico
            if pdf.get_y() + 100 > self.page_height - self.margins['bottom']:
                pdf.add_page()
                pdf.set_y(self.margins['top'])
            
            pdf.set_font('Arial', 'B', 10)
            pdf.cell(0, 10, 'Gráfico de Consumo:', 0, 1)
            pdf.image(graph_path, x=self.margins['left'], w=self.content_width)
            
            # Adicionar nova página para a assinatura se não houver espaço suficiente
            if pdf.get_y() + 50 > self.page_height - self.margins['bottom']:
                pdf.add_page()
            
            # Assinatura no final do documento - Centralizada
            pdf.ln(30)  # Espaço antes da assinatura
            
            # Linha de assinatura (centralizada)
            line_width = 60  # Largura da linha
            line_x = (self.page_width - line_width) / 2  # Centralizar horizontalmente
            line_y = pdf.get_y()
            pdf.line(line_x, line_y, line_x + line_width, line_y)
            
            # Nome do responsável (centralizado)
            pdf.set_font('Arial', '', 10)
            pdf.cell(0, 8, responsavel_tecnico, 0, 1, 'C')
            
            # Texto "Responsável Técnico" (centralizado)
            pdf.set_font('Arial', 'I', 8)
            pdf.cell(0, 5, "Responsável Técnico", 0, 1, 'C')
            
            # Salvar PDF temporário
            pdf.output(temp_pdf_path)
            
            # Verificar se o PDF temporário foi criado
            if not os.path.exists(temp_pdf_path):
                raise FileNotFoundError("PDF temporário não foi gerado corretamente")
            
            # Aplicar timbrado como background
            self.apply_timbrado(temp_pdf_path, output_path)
            
        except Exception as e:
            print(f"Erro durante a geração do PDF: {str(e)}")
            raise
        finally:
            # Limpar arquivos temporários
            if 'graph_path' in locals() and os.path.exists(graph_path):
                os.remove(graph_path)
            if 'temp_pdf_path' in locals() and os.path.exists(temp_pdf_path):
                os.remove(temp_pdf_path)

def main():
    if len(sys.argv) != 3:
        print("Uso: python gen-pdf.py <input_json_file> <output_pdf_file>")
        print("Nota: Este script é chamado automaticamente pelo send-pdf.py")
        sys.exit(1)
    
    input_json_file = sys.argv[1]
    output_pdf_file = sys.argv[2]
    
    try:
        # Verificar se o arquivo JSON existe
        if not os.path.exists(input_json_file):
            raise FileNotFoundError(f"Arquivo JSON de entrada não encontrado: {input_json_file}")
        
        # Ler o arquivo JSON de entrada
        with open(input_json_file, 'r', encoding='utf-8') as f:
            json_data = json.load(f)
        
        # Verificar estrutura do JSON
        required_keys = ['responsavel', 'data']
        if not all(key in json_data for key in required_keys):
            raise ValueError("JSON de entrada não contém a estrutura esperada")
        
        # Criar diretório de saída se não existir
        output_dir = os.path.dirname(output_pdf_file)
        if output_dir and not os.path.exists(output_dir):
            os.makedirs(output_dir)
        
        # Gerar PDF
        generator = PDFGenerator()
        generator.generate_pdf(json_data, output_pdf_file)
        
        print(f"PDF gerado com sucesso: {output_pdf_file}")
        
    except Exception as e:
        print(f"Erro ao gerar PDF: {str(e)}")
        sys.exit(1)

if __name__ == "__main__":
    main()