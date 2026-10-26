# -*- coding: utf-8 -*-
"""
Script to generate Laporan Pengembangan Tugas Mobile Apps SI-ASET (.docx)
Mata Kuliah: CIE511 Pemrograman Mobile
Universitas Esa Unggul
Format: Identasi paragraf (First Line Indent) dan Spasi Baris 1.5 (Line Spacing 1.5)
"""

import os
import docx
from docx.shared import Inches, Pt, RGBColor
from docx.enum.text import WD_ALIGN_PARAGRAPH, WD_TAB_ALIGNMENT, WD_TAB_LEADER
from docx.enum.table import WD_TABLE_ALIGNMENT, WD_ALIGN_VERTICAL
from docx.oxml import OxmlElement, parse_xml
from docx.oxml.ns import qn, nsdecls

def create_document():
    doc = docx.Document()

    # Configure Margins (Top 3cm, Left 3.5cm, Bottom 2.5cm, Right 2.5cm)
    for section in doc.sections:
        section.top_margin = Inches(1.18)     # ~3.0 cm
        section.bottom_margin = Inches(0.98)  # ~2.5 cm
        section.left_margin = Inches(1.38)    # ~3.5 cm
        section.right_margin = Inches(0.98)   # ~2.5 cm
        section.header_distance = Inches(0.5)
        section.footer_distance = Inches(0.5)

    # Base Colors
    NAVY_HEX = "13315C"
    NAVY_DARK_HEX = "0B2545"
    ORANGE_HEX = "FF7A00"
    GRAY_BG_HEX = "F4F6F9"
    BORDER_GRAY_HEX = "CBD5E1"
    
    NAVY_COLOR = RGBColor(19, 49, 92)
    ORANGE_COLOR = RGBColor(255, 122, 0)
    DARK_TEXT = RGBColor(30, 41, 59)
    MUTED_TEXT = RGBColor(100, 116, 139)

    # Style Configurations
    style_normal = doc.styles['Normal']
    font_normal = style_normal.font
    font_normal.name = 'Times New Roman'
    font_normal.size = Pt(12)
    font_normal.color.rgb = DARK_TEXT
    style_normal.paragraph_format.line_spacing = 1.5

    # Helper function for cell background color
    def set_cell_background(cell, fill_hex):
        tcPr = cell._element.get_or_add_tcPr()
        shd = parse_xml(f'<w:shd {nsdecls("w")} w:fill="{fill_hex}"/>')
        tcPr.append(shd)

    # Helper function for cell margins/padding
    def set_cell_margins(cell, top=100, bottom=100, left=150, right=150):
        tcPr = cell._element.get_or_add_tcPr()
        tcMar = parse_xml(f'<w:tcMar {nsdecls("w")}><w:top w:w="{top}" w:type="dxa"/><w:bottom w:w="{bottom}" w:type="dxa"/><w:left w:w="{left}" w:type="dxa"/><w:right w:w="{right}" w:type="dxa"/></w:tcMar>')
        tcPr.append(tcMar)

    # Helper function for cell borders
    def set_cell_borders(cell, top="none", bottom="none", left="none", right="none", 
                         color="CBD5E1", sz="4"):
        tcPr = cell._element.get_or_add_tcPr()
        borders = parse_xml(
            f'<w:tcBorders {nsdecls("w")}>'
            f'<w:top w:val="{top}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:left w:val="{left}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:bottom w:val="{bottom}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'<w:right w:val="{right}" w:sz="{sz}" w:space="0" w:color="{color}"/>'
            f'</w:tcBorders>'
        )
        tcPr.append(borders)

    # Helper for generic paragraph (no indent, customizable line spacing)
    def add_p(text="", align=WD_ALIGN_PARAGRAPH.LEFT, bold=False, italic=False, 
              size=12, color=DARK_TEXT, space_before=0, space_after=6, line_spacing=1.5):
        p = doc.add_paragraph()
        p.alignment = align
        p.paragraph_format.first_line_indent = Inches(0)
        p.paragraph_format.space_before = Pt(space_before)
        p.paragraph_format.space_after = Pt(space_after)
        p.paragraph_format.line_spacing = line_spacing
        if text:
            run = p.add_run(text)
            run.bold = bold
            run.italic = italic
            run.font.name = 'Times New Roman'
            run.font.size = Pt(size)
            run.font.color.rgb = color
        return p

    # Helper for standard body text paragraph WITH FIRST LINE INDENT & 1.5 LINE SPACING
    def add_body_p(text="", align=WD_ALIGN_PARAGRAPH.JUSTIFY, indent=0.45, line_spacing=1.5, space_before=0, space_after=6):
        p = doc.add_paragraph()
        p.alignment = align
        p.paragraph_format.first_line_indent = Inches(indent)
        p.paragraph_format.space_before = Pt(space_before)
        p.paragraph_format.space_after = Pt(space_after)
        p.paragraph_format.line_spacing = line_spacing
        if text:
            run = p.add_run(text)
            run.font.name = 'Times New Roman'
            run.font.size = Pt(12)
            run.font.color.rgb = DARK_TEXT
        return p

    def add_heading_1(text):
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.first_line_indent = Inches(0)
        p.paragraph_format.space_before = Pt(20)
        p.paragraph_format.space_after = Pt(10)
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.keep_with_next = True
        run = p.add_run(text)
        run.bold = True
        run.font.name = 'Times New Roman'
        run.font.size = Pt(14)
        run.font.color.rgb = NAVY_COLOR
        return p

    def add_heading_2(text):
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.first_line_indent = Inches(0)
        p.paragraph_format.space_before = Pt(16)
        p.paragraph_format.space_after = Pt(8)
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.keep_with_next = True
        run = p.add_run(text)
        run.bold = True
        run.font.name = 'Times New Roman'
        run.font.size = Pt(12.5)
        run.font.color.rgb = NAVY_COLOR
        return p

    def add_heading_3(text):
        p = doc.add_paragraph()
        p.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p.paragraph_format.first_line_indent = Inches(0)
        p.paragraph_format.space_before = Pt(12)
        p.paragraph_format.space_after = Pt(6)
        p.paragraph_format.line_spacing = 1.5
        p.paragraph_format.keep_with_next = True
        run = p.add_run(text)
        run.bold = True
        run.italic = True
        run.font.name = 'Times New Roman'
        run.font.size = Pt(12)
        run.font.color.rgb = NAVY_COLOR
        return p

    def add_bullet(bold_prefix, text, line_spacing=1.5):
        p = doc.add_paragraph(style='List Bullet')
        p.paragraph_format.first_line_indent = Inches(-0.2)
        p.paragraph_format.left_indent = Inches(0.4)
        p.paragraph_format.space_before = Pt(3)
        p.paragraph_format.space_after = Pt(4)
        p.paragraph_format.line_spacing = line_spacing
        if bold_prefix:
            r1 = p.add_run(bold_prefix)
            r1.bold = True
            r1.font.name = 'Times New Roman'
            r1.font.size = Pt(11.5)
            r1.font.color.rgb = DARK_TEXT
        r2 = p.add_run(text)
        r2.font.name = 'Times New Roman'
        r2.font.size = Pt(11.5)
        r2.font.color.rgb = DARK_TEXT
        return p

    # Callout Box / Screenshot Placeholder Generator
    def add_screenshot_placeholder(fig_no, title, screen_name, components_list, description):
        table = doc.add_table(rows=1, cols=1)
        table.alignment = WD_TABLE_ALIGNMENT.CENTER
        cell = table.cell(0, 0)
        set_cell_background(cell, "F8FAFC")
        set_cell_margins(cell, top=140, bottom=140, left=200, right=200)
        set_cell_borders(cell, top="single", bottom="single", left="single", right="single", color=ORANGE_HEX, sz="12")
        
        cp = cell.paragraphs[0]
        cp.alignment = WD_ALIGN_PARAGRAPH.CENTER
        cp.paragraph_format.first_line_indent = Inches(0)
        cp.paragraph_format.space_before = Pt(4)
        cp.paragraph_format.space_after = Pt(4)
        cp.paragraph_format.line_spacing = 1.15
        
        r_tag = cp.add_run("[PENANDA TANGKAPAN LAYAR FIGMA / UI MOBILE APPS]\n")
        r_tag.bold = True
        r_tag.font.name = 'Arial'
        r_tag.font.size = Pt(10)
        r_tag.font.color.rgb = ORANGE_COLOR
        
        r_fig = cp.add_run(f"{fig_no}: {title}\n")
        r_fig.bold = True
        r_fig.font.name = 'Arial'
        r_fig.font.size = Pt(11)
        r_fig.font.color.rgb = NAVY_COLOR
        
        r_box = cp.add_run(
            "\n"
            "+-----------------------------------------------------------------------------------------+\n"
            "|                                                                                         |\n"
            "|               [ TEMPATKAN TANGKAPAN LAYAR (SCREENSHOT FIGMA) DI SINI ]                  |\n"
            "|                                Ukuran Rasio: 9:16 (Mobile Android)                      |\n"
            "|                                                                                         |\n"
            "+-----------------------------------------------------------------------------------------+\n\n"
        )
        r_box.font.name = 'Consolas'
        r_box.font.size = Pt(9)
        r_box.font.color.rgb = MUTED_TEXT

        p_desc = cell.add_paragraph()
        p_desc.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p_desc.paragraph_format.first_line_indent = Inches(0)
        p_desc.paragraph_format.space_before = Pt(2)
        p_desc.paragraph_format.space_after = Pt(2)
        p_desc.paragraph_format.line_spacing = 1.15
        
        r_desc_lbl = p_desc.add_run("Deskripsi Layar & Alur UI: ")
        r_desc_lbl.bold = True
        r_desc_lbl.font.name = 'Arial'
        r_desc_lbl.font.size = Pt(9.5)
        r_desc_lbl.font.color.rgb = DARK_TEXT
        
        r_desc_txt = p_desc.add_run(description + "\n")
        r_desc_txt.font.name = 'Arial'
        r_desc_txt.font.size = Pt(9.5)
        r_desc_txt.font.color.rgb = DARK_TEXT

        p_comp = cell.add_paragraph()
        p_comp.alignment = WD_ALIGN_PARAGRAPH.LEFT
        p_comp.paragraph_format.first_line_indent = Inches(0)
        p_comp.paragraph_format.space_before = Pt(2)
        p_comp.paragraph_format.space_after = Pt(2)
        p_comp.paragraph_format.line_spacing = 1.15
        
        r_comp_lbl = p_comp.add_run("Elemen Komponen UI yang Dibuat: ")
        r_comp_lbl.bold = True
        r_comp_lbl.font.name = 'Arial'
        r_comp_lbl.font.size = Pt(9.5)
        r_comp_lbl.font.color.rgb = DARK_TEXT
        
        r_comp_txt = p_comp.add_run(", ".join(components_list))
        r_comp_txt.italic = True
        r_comp_txt.font.name = 'Arial'
        r_comp_txt.font.size = Pt(9.5)
        r_comp_txt.font.color.rgb = NAVY_COLOR

        p_sp = doc.add_paragraph()
        p_sp.paragraph_format.first_line_indent = Inches(0)
        p_sp.paragraph_format.space_before = Pt(4)
        p_sp.paragraph_format.space_after = Pt(6)

    # Code Block Generator
    def add_code_block(title, code_text, lang="Dart / Flutter"):
        table = doc.add_table(rows=1, cols=1)
        table.alignment = WD_TABLE_ALIGNMENT.CENTER
        cell = table.cell(0, 0)
        set_cell_background(cell, "F1F5F9")
        set_cell_margins(cell, top=100, bottom=100, left=150, right=150)
        set_cell_borders(cell, top="single", bottom="single", left="single", right="single", color=NAVY_HEX, sz="6")

        cp = cell.paragraphs[0]
        cp.paragraph_format.first_line_indent = Inches(0)
        cp.paragraph_format.space_before = Pt(2)
        cp.paragraph_format.space_after = Pt(2)
        cp.paragraph_format.line_spacing = 1.05
        
        r_hdr = cp.add_run(f"Source Code: {title} ({lang})\n")
        r_hdr.bold = True
        r_hdr.font.name = 'Consolas'
        r_hdr.font.size = Pt(9)
        r_hdr.font.color.rgb = NAVY_COLOR

        r_code = cp.add_run(code_text)
        r_code.font.name = 'Consolas'
        r_code.font.size = Pt(8.5)
        r_code.font.color.rgb = RGBColor(30, 41, 59)

        p_sp = doc.add_paragraph()
        p_sp.paragraph_format.first_line_indent = Inches(0)
        p_sp.paragraph_format.space_before = Pt(2)
        p_sp.paragraph_format.space_after = Pt(4)

    # Table generator helper
    def create_custom_table(headers, rows_data, col_widths=None):
        table = doc.add_table(rows=len(rows_data) + 1, cols=len(headers))
        table.alignment = WD_TABLE_ALIGNMENT.CENTER

        # Header Row
        hdr_cells = table.rows[0].cells
        for idx, title in enumerate(headers):
            hdr_cells[idx].text = title
            set_cell_background(hdr_cells[idx], NAVY_HEX)
            set_cell_margins(hdr_cells[idx], top=100, bottom=100, left=120, right=120)
            set_cell_borders(hdr_cells[idx], top="single", bottom="single", left="single", right="single", color=BORDER_GRAY_HEX, sz="4")
            p = hdr_cells[idx].paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.CENTER
            p.paragraph_format.first_line_indent = Inches(0)
            p.paragraph_format.line_spacing = 1.15
            for r in p.runs:
                r.bold = True
                r.font.name = 'Times New Roman'
                r.font.size = Pt(10)
                r.font.color.rgb = RGBColor(255, 255, 255)

        # Body Rows
        for r_idx, row in enumerate(rows_data):
            row_cells = table.rows[r_idx + 1].cells
            bg_color = "FFFFFF" if r_idx % 2 == 0 else "F8FAFC"
            for c_idx, val in enumerate(row):
                row_cells[c_idx].text = str(val)
                set_cell_background(row_cells[c_idx], bg_color)
                set_cell_margins(row_cells[c_idx], top=80, bottom=80, left=100, right=100)
                set_cell_borders(row_cells[c_idx], top="single", bottom="single", left="single", right="single", color=BORDER_GRAY_HEX, sz="4")
                p = row_cells[c_idx].paragraphs[0]
                p.paragraph_format.first_line_indent = Inches(0)
                p.paragraph_format.line_spacing = 1.15
                if c_idx == 0 and len(row) > 2 and len(str(val)) <= 4:
                    p.alignment = WD_ALIGN_PARAGRAPH.CENTER
                else:
                    p.alignment = WD_ALIGN_PARAGRAPH.LEFT
                for r in p.runs:
                    r.font.name = 'Times New Roman'
                    r.font.size = Pt(9.5)
                    r.font.color.rgb = DARK_TEXT

        if col_widths:
            for row in table.rows:
                for idx, width in enumerate(col_widths):
                    row.cells[idx].width = Inches(width)

        p_sp = doc.add_paragraph()
        p_sp.paragraph_format.first_line_indent = Inches(0)
        p_sp.paragraph_format.space_before = Pt(2)
        p_sp.paragraph_format.space_after = Pt(6)
        return table

    print("Building Document Content with First Line Indent and 1.5 Line Spacing...")

    # ==========================================
    # 1. COVER / HALAMAN JUDUL
    # ==========================================
    add_p("LAPORAN TUGAS PENGEMBANGAN LANJUTAN", align=WD_ALIGN_PARAGRAPH.CENTER, bold=True, size=14, color=NAVY_COLOR, space_before=12, space_after=4, line_spacing=1.3)
    add_p("MATA KULIAH CIE511 PEMROGRAMAN MOBILE", align=WD_ALIGN_PARAGRAPH.CENTER, bold=True, size=13, color=DARK_TEXT, space_before=2, space_after=12, line_spacing=1.3)

    add_p("PERANCANGAN METODOLOGI MOBILE-D, ARSITEKTUR MODEL, DESAIN USER INTERFACE (FIGMA), DAN USER EXPERIENCE PADA APLIKASI SISTEM INFORMASI MANAJEMEN ASET PERUSAHAAN (SI-ASET) BERBASIS ANDROID FLUTTER DAN RESTFUL API LARAVEL",
          align=WD_ALIGN_PARAGRAPH.CENTER, bold=True, size=13, color=NAVY_COLOR, space_before=8, space_after=24, line_spacing=1.4)

    add_p("Dosen Pengampu: Ir. Sawali Wahyu, S.Kom., M.Kom.", align=WD_ALIGN_PARAGRAPH.CENTER, italic=True, bold=True, size=12, color=DARK_TEXT, space_before=4, space_after=24, line_spacing=1.3)

    add_p("Disusun oleh: Kelompok 1", align=WD_ALIGN_PARAGRAPH.CENTER, bold=True, size=12, color=NAVY_COLOR, space_before=4, space_after=8, line_spacing=1.3)

    # Table of Members
    member_data = [
        ["20240801334", "Abuzar Ghiffari", "Ketua Kelompok"],
        ["20240801310", "Aisyah Nabila Miftakhul Jannah", "Anggota"],
        ["20240801313", "Ricki Cahyadi", "Anggota"],
        ["20240801328", "Wilbert Christopher", "Anggota"],
        ["20240801321", "Steveen", "Anggota"],
        ["20240801343", "Ignatius Stefan Bryan B", "Anggota"]
    ]
    t_members = doc.add_table(rows=len(member_data), cols=3)
    t_members.alignment = WD_TABLE_ALIGNMENT.CENTER
    for idx, (nim, nama, jabatan) in enumerate(member_data):
        row = t_members.rows[idx]
        row.cells[0].text = nim
        row.cells[1].text = nama
        row.cells[2].text = jabatan
        for c in row.cells:
            set_cell_background(c, "FFFFFF")
            set_cell_margins(c, top=40, bottom=40, left=80, right=80)
            set_cell_borders(c, top="none", bottom="none", left="none", right="none")
            p = c.paragraphs[0]
            p.alignment = WD_ALIGN_PARAGRAPH.LEFT
            p.paragraph_format.first_line_indent = Inches(0)
            p.paragraph_format.line_spacing = 1.15
            for r in p.runs:
                r.font.name = 'Times New Roman'
                r.font.size = Pt(11)
                r.font.color.rgb = DARK_TEXT

    p_sp = doc.add_paragraph()
    p_sp.paragraph_format.first_line_indent = Inches(0)
    p_sp.paragraph_format.space_before = Pt(28)

    add_p("PROGRAM STUDI TEKNIK INFORMATIKA\nFAKULTAS ILMU KOMPUTER\nUNIVERSITAS ESA UNGGUL\n2026", 
          align=WD_ALIGN_PARAGRAPH.CENTER, bold=True, size=12, color=NAVY_COLOR, space_before=24, space_after=12, line_spacing=1.3)

    doc.add_page_break()

    # ==========================================
    # KATA PENGANTAR
    # ==========================================
    add_p("KATA PENGANTAR", align=WD_ALIGN_PARAGRAPH.CENTER, bold=True, size=14, color=NAVY_COLOR, space_before=12, space_after=14, line_spacing=1.5)

    add_body_p(
        "Puji dan syukur kami panjatkan ke hadirat Tuhan Yang Maha Esa atas berkat, rahmat, dan karunia-Nya, kami dari Kelompok 1 Program Studi Teknik Informatika, Fakultas Ilmu Komputer, Universitas Esa Unggul dapat menyelesaikan laporan tugas pengembangan lanjutan untuk mata kuliah CIE511 Pemrograman Mobile dengan judul 'Perancangan Metodologi Mobile-D, Arsitektur Model, Desain User Interface (Figma), dan User Experience pada Aplikasi Sistem Informasi Manajemen Aset Perusahaan (SI-ASET) Berbasis Android Flutter dan RESTful API Laravel'. Laporan ini disusun secara komprehensif dengan menerapkan standar format penulisan akademik, termasuk penerapan identasi paragraf dan spasi 1,5."
    )

    add_body_p(
        "Laporan ini merupakan kelanjutan resmi dari perancangan spesifikasi kebutuhan sistem (functional & non-functional requirements), pemodelan diagram UML lengkap, dan perancangan basis data relasional 35 tabel dalam bentuk normal ketiga (3NF) yang telah dirumuskan pada tugas sebelumnya. Pada tahap pengembangan lanjutan ini, fokus utama kelompok kami diarahkan secara khusus pada modul aplikasi mobile Android (pada folder direktori asetkelompok/android/) serta keterhubungannya dengan RESTful API backend Laravel 12 (pada direktori asetkelompok/app/Http/Controllers/Api/)."
    )

    add_body_p(
        "Di dalam laporan ini, kami menguraikan empat pilar perancangan mobile utama yang menjadi instruksi penugasan dosen, yaitu:\n"
        "1. Penentuan dan elaborasi tahapan Metodologi Pengembangan Aplikasi Mobile-D yang disesuaikan secara presisi dengan karakteristik siklus iterasi proyek SI-ASET;\n"
        "2. Perancangan Arsitektur Model Mobile Apps berbasis Layered Clean Architecture dan Model-View-ViewModel (MVVM) menggunakan state management Provider;\n"
        "3. Perancangan Desain User Interface (UI) berbasis Figma / Adobe XD yang mencakup seluruh komponen visual (form, button, toggle switch, dynamic header, bottom nav bar, scanner overlay, dan signature canvas) disertai penanda tangkapan layar visual yang sistematis;\n"
        "4. Perumusan Konsep Desain User Experience (UX) berlandaskan 10 prinsip heuristik Nielsen, ergonomi jempol (thumb-zone), Poka-Yoke error prevention, serta aksesibilitas kontras WCAG 2.1 AA."
    )

    add_body_p(
        "Kami mengucapkan terima kasih yang sebesar-besarnya kepada Bapak Ir. Sawali Wahyu, S.Kom., M.Kom. selaku Dosen Pengampu atas arahan, bimbingan, dan transfer ilmu yang sangat berharga sepanjang perkuliahan Pemrograman Mobile. Kami menyadari bahwa laporan ini masih memiliki ruang penyempurnaan, oleh karena itu kami sangat terbuka menerima masukan serta kritik konstruktif demi kesempurnaan implementasi aplikasi ini di masa mendatang."
    )

    add_p("Bekasi, 26 Oktober 2026\nKelompok 1", align=WD_ALIGN_PARAGRAPH.RIGHT, bold=True, size=11.5, color=DARK_TEXT, space_before=12, space_after=12, line_spacing=1.3)

    doc.add_page_break()

    # ==========================================
    # DAFTAR ISI
    # ==========================================
    add_p("DAFTAR ISI", align=WD_ALIGN_PARAGRAPH.CENTER, bold=True, size=14, color=NAVY_COLOR, space_before=12, space_after=14, line_spacing=1.5)

    daftar_isi_data = [
        ["KATA PENGANTAR", "2"],
        ["DAFTAR ISI", "3"],
        ["BAB I METODOLOGI PENGEMBANGAN APLIKASI (MOBILE-D)", "4"],
        ["   1.1 Rasional Pemilihan Metodologi Mobile-D", "4"],
        ["   1.2 Tahapan dan Implementasi Mobile-D pada Proyek SI-ASET", "5"],
        ["       1.2.1 Explore (Eksplorasi Kebutuhan dan Studi Kelayakan)", "5"],
        ["       1.2.2 Initialize (Inisialisasi Tooling dan Arsitektur Awal)", "6"],
        ["       1.2.3 Production (Siklus Iterasi TDD & Core Feature Development)", "7"],
        ["       1.2.4 System Test (Uji Kompatibilitas, Integrasi API, dan Latensi)", "9"],
        ["       1.2.5 Fixize (Stabilisasi Akhir, Release Signing, dan Bundling APK)", "10"],
        ["   1.3 Matriks Waktu dan Pembagian Peran Kerja Tim Mahasiswa", "11"],
        ["BAB II PERANCANGAN ARSITEKTUR MODEL MOBILE APPS", "12"],
        ["   2.1 Pola Arsitektur Layered Clean Architecture & MVVM", "12"],
        ["   2.2 Rincian Lapisan Arsitektur (Layer Breakdown)", "13"],
        ["       2.2.1 Presentation Layer (UI Widgets & Screens)", "13"],
        ["       2.2.2 State Management & Provider Layer (ViewModel)", "14"],
        ["       2.2.3 Service & Network Layer (RESTful API Client)", "15"],
        ["       2.2.4 Domain & Model Layer (Serialization/Deserialization)", "16"],
        ["       2.2.5 Local Persistence Layer (Session & Storage)", "17"],
        ["   2.3 Mekanisme Komunikasi RESTful API dengan Backend Laravel Sanctum", "18"],
        ["   2.4 Cuplikan Kode Implementasi Arsitektur Terpilih", "19"],
        ["BAB III DESAIN USER INTERFACE APLIKASI MOBILE (FIGMA)", "23"],
        ["   3.1 Sistem Desain Visual (Design System Tokens)", "23"],
        ["       3.1.1 Tipografi dan Skala Hierarki Teks (Plus Jakarta Sans)", "23"],
        ["       3.1.2 Palet Warna Korporat (Navy Blue & Vibrant Orange)", "24"],
        ["       3.1.3 Komponen Interaktif (Header, Menu, Button, Toggle, Form)", "25"],
        ["   3.2 Katalog Penanda Tampilan UI Mobile Apps (Figma / Adobe XD)", "27"],
        ["BAB IV KONSEP DESAIN USER EXPERIENCE (UX) APLIKASI MOBILE", "38"],
        ["   4.1 Filosofi User-Centered Design (UCD) pada Lingkungan Kerja Fisik", "38"],
        ["   4.2 Penerapan 10 Prinsip Heuristik Usability Jakob Nielsen", "39"],
        ["   4.3 Alur Pengalaman Pengguna (User Journey & Task Flows)", "41"],
        ["   4.4 Desain Ergonomi Jempol (Thumb-Zone) dan Pencegahan Kesalahan (Poka-Yoke)", "43"],
        ["   4.5 Standar Aksesibilitas WCAG 2.1 AA dan Optimasi Lingkungan Nyata", "44"],
        ["BAB V KESIMPULAN DAN SARAN PENGEMBANGAN", "46"],
        ["   5.1 Kesimpulan", "46"],
        ["   5.2 Saran Pengembangan Lanjutan", "47"],
        ["DAFTAR PUSTAKA", "48"],
    ]

    for item, page in daftar_isi_data:
        p = doc.add_paragraph()
        p.paragraph_format.first_line_indent = Inches(0)
        p.paragraph_format.space_before = Pt(1.5)
        p.paragraph_format.space_after = Pt(2)
        p.paragraph_format.line_spacing = 1.3
        
        # Atur Right Tab Stop di batas margin kanan (6.1 inci / ~15.5 cm) dengan Dot Leader (2 .....)
        p.paragraph_format.tab_stops.add_tab_stop(Inches(6.1), WD_TAB_ALIGNMENT.RIGHT, WD_TAB_LEADER.DOTS)

        r1 = p.add_run(item)
        r1.font.name = 'Times New Roman'
        r1.font.size = Pt(11)
        if item.startswith("BAB") or item in ["KATA PENGANTAR", "DAFTAR ISI", "DAFTAR PUSTAKA"]:
            r1.bold = True
            r1.font.color.rgb = NAVY_COLOR
        else:
            r1.font.color.rgb = DARK_TEXT

        # Karakter Tab untuk memicu leader titik-titik otomatis sampai ke nomor halaman di margin kanan
        r_tab = p.add_run("\t")
        r_tab.font.name = 'Times New Roman'
        r_tab.font.size = Pt(11)

        r2 = p.add_run(page)
        r2.font.name = 'Times New Roman'
        r2.font.size = Pt(11)
        r2.bold = True
        r2.font.color.rgb = NAVY_COLOR

    doc.add_page_break()

    # ==========================================
    # BAB I: METODOLOGI PENGEMBANGAN APLIKASI (MOBILE-D)
    # ==========================================
    add_heading_1("BAB I METODOLOGI PENGEMBANGAN APLIKASI (MOBILE-D)")

    add_heading_2("1.1 Rasional Pemilihan Metodologi Mobile-D")
    add_body_p(
        "Dalam rekayasa perangkat lunak modern, pemilihan metodologi pengembangan memegang peranan krusial terhadap keberhasilan implementasi produk, khususnya pada platform perangkat bergerak (mobile). Aplikasi mobile memiliki karakteristik unik yang sangat berbeda dibandingkan dengan aplikasi web atau desktop konvensional. Aplikasi mobile beroperasi di atas lingkungan komputasi dengan sumber daya perangkat keras terbatas (seperti kapasitas RAM, efisiensi konsumsi daya baterai, dan siklus prosesor), ketergantungan erat pada sensor dan periferal perangkat keras native (kamera resolusi tinggi untuk barcode scanning serta touchscreen capacitive untuk tanda tangan digital), konektivitas jaringan nirkabel yang fluktuatif (transisi antara Wi-Fi dan sinyal seluler 4G/5G), serta tuntutan siklus iterasi yang sangat cepat (fast-paced release cycle)."
    )
    add_body_p(
        "Berdasarkan analisis karakteristik proyek Sistem Informasi Manajemen Aset Perusahaan (SI-ASET), kelompok kami memutuskan untuk menerapkan Metodologi Mobile-D. Metodologi Mobile-D merupakan metode pengembangan tangkas (agile development methodology) yang dirumuskan secara khusus oleh tim peneliti VTT Technical Research Centre of Finland (Abrahamsson et al., 2004) untuk menjawab tantangan pengembangan sistem perangkat lunak perangkat bergerak secara terstruktur, cepat, dan teruji."
    )
    add_body_p(
        "Alasan fundamental kelompok kami memilih Mobile-D dibandingkan metodologi lain (seperti Waterfall, Prototyping sederhana, atau Scrum umum) adalah sebagai berikut:"
    )
    add_bullet("1. Kustomisasi Spesifik untuk Lingkungan Mobile: ", "Mobile-D secara inheren memadukan ketatnya prinsip Test-Driven Development (TDD) dari Extreme Programming (XP), penataan arsitektur perangkat keras dari Rational Unified Process (RUP), dan skalabilitas manajemen dari Crystal Methodology. Hal ini menjamin kestabilan modul sensor kamera scanner dan canvas e-signature pada perangkat smartphone Android.")
    add_bullet("2. Siklus Iterasi Singkat dan Terukur: ", "Mobile-D membagi pengerjaan ke dalam 5 fase terstruktur dengan siklus harian dan mingguan yang ketat. Pendekatan ini memungkinkan tim mahasiswa Kelompok 1 mengevaluasi fungsi aplikasi secara bertahap bersamaan dengan penyempurnaan endpoint RESTful API Laravel.")
    add_bullet("3. Minimalisasi Risiko Ketidaksesuaian Hardware: ", "Adanya fase Initialize dan System Test khusus menjamin bahwa integrasi pustaka eksternal seperti mobile_scanner, printing PDF, dan SharedPreferences dapat divalidasi sedini mungkin sebelum aplikasi dirilis ke tahap stabilisasi.")

    add_heading_2("1.2 Tahapan dan Implementasi Mobile-D pada Proyek SI-ASET")
    add_body_p(
        "Metodologi Mobile-D diimplementasikan ke dalam lima tahapan sekuensial yang berfokus pada kualitas kode, kestabilan arsitektur, dan kepuasan pengguna akhir. Setiap tahapan diuraikan secara mendalam sebagai berikut:"
    )

    add_heading_3("1.2.1 Explore (Eksplorasi Kebutuhan dan Studi Kelayakan)")
    add_body_p(
        "Fase Explore bertujuan untuk menyelaraskan pemahaman seluruh anggota kelompok terhadap ruang lingkup permasalahan pengelolaan aset fisik perusahaan serta menentukan kelayakan teknologi yang akan diadopsi. Pada fase ini, kelompok kami melakukan aktivitas:"
    )
    add_bullet("a. Analisis Pemangku Kepentingan (Stakeholders): ", "Mendefinisikan 4 aktor utama pengguna sistem, yaitu Admin (pengelola master organisasi dan user), Kepala Kantor (otoritas persetujuan anggaran pengadaan lokal), Manajer (penerbit SPK, validator temuan opname, dan pengesah BAST), serta Staf Teknisi (eksekutor lapangan, scanner aset, dan pelapor kondisi fisik).")
    add_bullet("b. Perumusan User Stories dan Kebutuhan Fungsional: ", "Menyusun backlog kebutuhan awal, antara lain kemampuan pemindaian barcode/QR code di bawah 500 ms, penandatanganan Berita Acara Serah Terima (BAST) langsung di atas layar smartphone, serta perhitungan beban penyusutan depresiasi otomatis.")
    add_bullet("c. Studi Kelayakan Teknologi (Technology Feasibility): ", "Memilih framework Flutter 3.13 (Dart) untuk membangun aplikasi Android yang responsif dan performan, dipadukan dengan backend framework Laravel 12 (PHP 8.2+) dengan autentikasi stateless token Laravel Sanctum dan basis data relasional MySQL 8.0.")
    add_bullet("d. Output Fase Explore: ", "Dokumen spesifikasi kebutuhan perangkat lunak (SRS/PRD), daftar use case fungsional (UC-01 hingga UC-13), serta batasan arsitektur sistem.")

    add_heading_3("1.2.2 Initialize (Inisialisasi Tooling dan Arsitektur Awal)")
    add_body_p(
        "Fase Initialize berfokus pada persiapan infrastruktur pengembangan, penetapan standar coding tim, dan penyusunan fondasi arsitektur perangkat lunak agar proses produksi berjalan lancar tanpa friksi teknis. Aktivitas yang dilakukan meliputi:"
    )
    add_bullet("a. Penyiapan Repositori & Environment: ", "Inisialisasi workspace di direktori asetkelompok/android/ dan asetkelompok/, konfigurasi Flutter SDK, Android Studio, Gradle build tools (target SDK minimal Android 8.0 Oreo API level 26 hingga Android 14 API level 34).")
    add_bullet("b. Instalasi Dependensi Pustaka Inti: ", "Mendefinisikan dependensi pada pubspec.yaml, mencakup provider (^6.1.5) untuk state management, http (^1.6.0) untuk networking, shared_preferences (^2.5.6) untuk token persistence, mobile_scanner (^7.4.2) untuk decoding kamera barcode, printing (^5.15.1) dan pdf (^3.13.1) untuk pembuatan dokumen BAST, serta google_fonts (^9.0.0) untuk tipografi korporat.")
    add_bullet("c. Perancangan Kontrak API (API Contract Baseline): ", "Menyusun skema JSON request-response standar bersama pengembang backend untuk rute autentikasi (/api/v1/auth/login), pencarian kode aset (/api/v1/aset/scan/{kode}), penugasan kerja (/api/v1/penugasan), dan BAST.")
    add_bullet("d. Output Fase Initialize: ", "Kerangka proyek (scaffolding) Flutter yang terstruktur dalam direktori core/, models/, providers/, services/, dan screens/, serta skema basis data awal di backend Laravel.")

    add_heading_3("1.2.3 Production (Siklus Iterasi TDD & Core Feature Development)")
    add_body_p(
        "Fase Production merupakan jantung dari metode Mobile-D di mana implementasi kode dilakukan melalui iterasi-iterasi terfokus menggunakan prinsip Test-Driven Development (TDD). Tim mengimplementasikan fitur ke dalam tiga siklus loop iterasi:"
    )
    add_bullet("1. Core Loop (Siklus Inti - Iterasi 1): ", "Fokus pada pembangunan modul-modul fundamental. Mengembangkan ApiService singleton, modul autentikasi login terenkripsi Sanctum, penanganan token bearer, sistem dynamic dashboard adaptif sesuai peran pengguna (Admin/Manajer/Staf), serta kerangka navigasi utama (Bottom Navigation Bar 5 tab).")
    add_bullet("2. Integration Loop (Siklus Integrasi - Iterasi 2): ", "Fokus pada interaksi perangkat keras dan logika operasional lapangan. Mengembangkan layar pemindai barcode/QR code memanfaatkan kamera native Android via mobile_scanner dengan auto-focus dan toggle senter flash, alur form penerbitan Surat Perintah Kerja (SPK) penugasan staf oleh manajer, checklist kondisi aset lapangan, serta form unggah bukti foto kerusakan fisik melalui kamera.")
    add_bullet("3. Stabilization Loop (Siklus Stabilisasi Fitur - Iterasi 3): ", "Fokus pada alur bisnis lanjutan dan validasi dokumen. Mengembangkan kanvas tanda tangan digital pada modul BAST (Berita Acara Serah Terima), penarikan file PDF laporan BAST yang telah ber-hash SHA-256 dan siap dicetak melalui koneksi printer nirkabel via pustaka printing, modul pemantauan jadwal servis pemeliharaan preventif, serta kartu kalkulasi depresiasi nilai buku aset.")
    add_bullet("d. Output Fase Production: ", "Aplikasi mobile SI-ASET versi fungsional penuh (working prototype build) yang terhubung dinamis ke server backend Laravel 12.")

    add_heading_3("1.2.4 System Test (Uji Kompatibilitas, Integrasi API, dan Latensi)")
    add_body_p(
        "Pada fase System Test, aplikasi diuji secara ketat untuk menjamin keandalan fungsional, performa, dan kompatibilitas sebelum dinyatakan siap rilis. Pengujian mencakup:"
    )
    add_bullet("a. Pengujian Fungsional Black-Box: ", "Menguji seluruh skenario kasus uji use case (UC-01 hingga UC-13), memastikan validasi form penugasan berhasil menolak input tanpa foto bukti, penguncian nomor dokumen otomatis (SELECT ... FOR UPDATE) tidak memicu duplikasi, dan otorisasi hak akses peran (RBAC) berjalan sempurna.")
    add_bullet("b. Pengujian Integrasi Kamera & Hardware: ", "Menguji kecepatan respons decoding QR Code dan Barcode Code 128 pada berbagai tingkat intensitas pencahayaan (terang, redup, dan menggunakan lampu flash senter), memastikan latensi decode tetap di bawah ambang batas NFR-02 (< 500 ms).")
    add_bullet("c. Pengujian Kompatibilitas Perangkat (Device Matrix Testing): ", "Menjalankan aplikasi pada perangkat Android fisik (Samsung Galaxy Series, Xiaomi Redmi Series) serta Android Emulator dengan variasi ukuran layar (5.5 inci hingga 6.7 inci) dan versi OS Android 8.0, 10, 12, hingga Android 14.")
    add_bullet("d. Pengujian Latensi Jaringan: ", "Mengukur respons time panggilan HTTP REST API pada jaringan Wi-Fi dan 4G seluler, memastikan rata-rata waktu respons berada di bawah 1.5 detik (sesuai kriteria lolos uji NFR-01).")
    add_bullet("e. Output Fase System Test: ", "Laporan verifikasi pengujian sistem (Defect Tracking Report) dan daftar perbaikan minor.")

    add_heading_3("1.2.5 Fixize (Stabilisasi Akhir, Release Signing, dan Bundling APK)")
    add_body_p(
        "Fase terakhir dari Mobile-D adalah Fixize, yang bertujuan memfinalisasi perangkat lunak menjadi paket distribusi rilis yang aman, stabil, dan siap operasional:"
    )
    add_bullet("a. Bug Fixing & Polishing: ", "Memperbaiki cacat minor yang ditemukan pada fase System Test, memastikan konsistensi tema gelap/terang, dan mengoptimalkan memori widget.")
    add_bullet("b. Pembersihan Kode & Keamanan: ", "Menghapus pesan debug logging sensitif, memastikan kata sandi dan token bearer tersimpan aman di SharedPreferences terisolasi.")
    add_bullet("c. Konfigurasi ProGuard dan R8: ", "Mengaktifkan minification dan code shrinking pada android/app/build.gradle guna memperkecil ukuran berkas biner dan mencegah reverse-engineering.")
    add_bullet("d. Release Signing & Packaging: ", "Membuat keystore resmi rilis Android dan mengompilasi bundel aplikasi final menjadi berkas si_aset_mobile.apk.")
    add_bullet("e. Output Fase Fixize: ", "Paket APK produksi siap instalasi, dokumentasi panduan operasional pengguna, dan repositori kode terarsip.")

    add_heading_2("1.3 Matriks Waktu dan Pembagian Peran Kerja Tim Mahasiswa")
    add_body_p(
        "Pelaksanaan tahapan Mobile-D pada proyek SI-ASET oleh Kelompok 1 dijadwalkan secara sistematis selama rentang waktu pengerjaan tugas akademik. Tabel 1.1 merangkum alokasi durasi, aktivitas utama, dan peran penanggung jawab masing-masing anggota tim:"
    )

    t_mobile_d_headers = ["Fase Mobile-D", "Durasi", "Aktivitas Utama & Deliverables", "PIC Penanggung Jawab"]
    t_mobile_d_rows = [
        ["1. Explore", "1 Minggu", "Analisis kebutuhan sistem, pemodelan use case, identifikasi 4 peran, studi kelayakan Flutter & Sanctum.", "Abuzar Ghiffari & Ricki Cahyadi"],
        ["2. Initialize", "1 Minggu", "Setup Flutter 3.13, pubspec.yaml, ApiService baseline, perancangan kontrak endpoint API Laravel.", "Ricki Cahyadi & Steveen"],
        ["3. Production\n(Iterasi 1 - Core)", "2 Minggu", "State Provider (Auth & Theme), login screen, role-based dashboard, bottom navigation bar.", "Aisyah Nabila & Wilbert Christopher"],
        ["4. Production\n(Iterasi 2 - Integration)", "2 Minggu", "Mobile scanner kamera, form SPK penugasan staf, checklist audit fisik, upload foto kegiatan.", "Abuzar Ghiffari & Ignatius Stefan"],
        ["5. Production\n(Iterasi 3 - Stabilization)", "1.5 Minggu", "Kanvas e-signature BAST, rendering PDF cetak, kalkulasi depresiasi, notifikasi in-app.", "Steveen & Ricki Cahyadi"],
        ["6. System Test", "1 Minggu", "Uji black-box, uji kamera barcode (<500ms), uji latensi API (<1.5s), uji Android 8-14.", "Wilbert Christopher & Aisyah Nabila"],
        ["7. Fixize", "0.5 Minggu", "Code freeze, bug fixing, ProGuard/R8 obfuscation, release signing, kompilasi file APK final.", "Seluruh Anggota Tim (Kelompok 1)"]
    ]
    create_custom_table(t_mobile_d_headers, t_mobile_d_rows, [1.3, 0.9, 2.9, 1.8])

    doc.add_page_break()

    # ==========================================
    # BAB II: PERANCANGAN ARSITEKTUR MODEL MOBILE APPS
    # ==========================================
    add_heading_1("BAB II PERANCANGAN ARSITEKTUR MODEL MOBILE APPS")

    add_heading_2("2.1 Pola Arsitektur Layered Clean Architecture & MVVM")
    add_body_p(
        "Untuk menjamin kode program mudah dipelihara (maintainability), mudah diuji (testability), serta memiliki pemisahan tanggung jawab yang tegas (Separation of Concerns), arsitektur aplikasi mobile SI-ASET dirancang dengan mengadopsi pola kombinasi Layered Clean Architecture dan Model-View-ViewModel (MVVM) berbasis Provider Pattern."
    )
    add_body_p(
        "Dalam pola ini, komponen antarmuka pengguna (View) tidak pernah berinteraksi secara langsung dengan basis data atau memanipulasi data mentah jaringan secara sembarangan. Sebaliknya, View berkomunikasi secara terisolasi melalui ChangeNotifier / State Provider (ViewModel), yang bertindak sebagai jembatan logika presentasi. Provider selanjutnya mendelegasikan kebutuhan data ke Service Layer (Network Client) yang berkomunikasi secara terenkripsi dengan RESTful API backend Laravel 12."
    )
    add_body_p(
        "Pendekatan ini memberikan keunggulan teknis yang signifikan bagi tim pengembang:\n"
        "1. Dekoupling UI dan Logika Bisnis: Perubahan desain antarmuka pada widget Flutter tidak akan merusak logika pengambilan data token atau algoritma persetujuan dokumen.\n"
        "2. Single Source of Truth: Status autentikasi pengguna dan preferensi tema dikelola secara tersentralisasi pada AuthProvider dan ThemeProvider, sehingga pembaruan data langsung memicu rendering ulang otomatis (reactive UI) pada komponen yang berlangganan.\n"
        "3. Kemudahan Mocking dan Pengujian: Service layer dapat dengan mudah diuji secara independen tanpa bergantung pada keberadaan tampilan layar."
    )

    add_heading_2("2.2 Rincian Lapisan Arsitektur (Layer Breakdown)")
    add_body_p(
        "Secara struktural, kode program aplikasi Android pada direktori asetkelompok/android/lib/ diorganisasikan ke dalam lima lapisan arsitektur berjenjang sebagaimana diuraikan berikut:"
    )

    add_heading_3("2.2.1 Presentation Layer (UI Widgets & Screens)")
    add_body_p(
        "Lapisan presentasi bertanggung jawab menampilkan antarmuka visual kepada pengguna serta menangkap interaksi gestur (tap, scroll, form submit). Lapisan ini terletak pada direktori lib/screens/ dan lib/core/widgets/, yang terdiri dari:"
    )
    add_bullet("a. Layar Utama (Screens): ", "LoginScreen, DashboardScreen, AsetListScreen, AsetDetailScreen, ScanScreen, OperasionalHubScreen, PenugasanFormScreen, PenugasanDetailScreen, BastListScreen, BastDetailScreen, LaporanScreen, NotifikasiScreen, dan ProfilScreen.")
    add_bullet("b. Navigasi Terpusat: ", "MainNavigationScreen menggunakan widget IndexedStack untuk mempertahankan state halaman ketika pengguna berpindah antartab pada NavigationBar Material 3.")
    add_bullet("c. Reusable UI Components: ", "GlassCard (kontainer efek kaca modern dengan dekorasi border halus) dan CustomBadge (chip penanda status semantik berwarna adaptif untuk kondisi aset dan status tugas).")

    add_heading_3("2.2.2 State Management & Provider Layer (ViewModel)")
    add_body_p(
        "Lapisan ini mengelola status reaktif aplikasi dan berperan sebagai ViewModel. Terletak pada direktori lib/core/providers/, lapisan ini memanfaatkan pustaka provider dengan dua provider utama:"
    )
    add_bullet("a. AuthProvider: ", "Mewarisi kelas ChangeNotifier. Mengelola siklus hidup sesi pengguna (login, logout, pengecekan token tersimpan), menyimpan objek PenggunaModel yang sedang aktif, dan menyediakan getter boolean isAuthenticated serta status loading. Menggunakan context.watch<AuthProvider>() untuk memperbarui UI saat status sesi berubah.")
    add_bullet("b. ThemeProvider: ", "Mengatur preferensi tema aplikasi (Light Mode vs. Dark Mode), menyinkronkan pengaturan ke SharedPreferences lokal, dan menyediakan ThemeMode yang dikonsumsi oleh MaterialApp pada main.dart.")

    add_heading_3("2.2.3 Service & Network Layer (RESTful API Client)")
    add_body_p(
        "Lapisan layanan jaringan bertanggung jawab mengeksekusi panggilan HTTP ke backend server Laravel. Terletak pada lib/core/services/api_service.dart, lapisan ini dirancang menggunakan pola desain Singleton (ApiService._internal()) untuk menjamin efisiensi alokasi memori:"
    )
    add_bullet("a. Otomatisasi Header dan Interceptor: ", "Secara otomatis menyisipkan header 'Accept: application/json', 'Content-Type: application/json', serta header 'Authorization: Bearer <token>' pada setiap request terproteksi.")
    add_bullet("b. Robust Network Handling: ", "Dilengkapi batas waktu timeout (15 detik) dan penanganan galat jaringan (SocketException) yang menghasilkan pesan ramah pengguna jika koneksi backend terputus.")
    add_bullet("c. Dukungan Multipart Form Data: ", "Menyediakan metode khusus untuk mengunggah berkas biner kamera (foto bukti fisik kegiatan staf dan gambar spesimen tanda tangan digital) ke endpoint backend.")

    add_heading_3("2.2.4 Domain & Model Layer (Serialization/Deserialization)")
    add_body_p(
        "Lapisan model data terletak pada direktori lib/models/. Setiap model merepresentasikan entitas bisnis sistem yang memetakan struktur JSON respons API ke objek Dart yang bertipe kuat (type-safe):"
    )
    add_bullet("a. PenggunaModel: ", "Memetakan id, nama, username, email, nomor_induk_karyawan, jabatan, relasi PeranModel, dan relasi KantorModel.")
    add_bullet("b. AsetModel: ", "Memetakan id, nomor_inventaris, barcode, nama_aset, merk, tipe, nomor_seri, status_kondisi, status_operasional, status_kepemilikan, harga_perolehan, nilai_residu, masa_manfaat_tahun, dan relasi kategori.")
    add_bullet("c. PenugasanModel: ", "Memetakan id, nomor_penugasan, judul_tugas, jenis_penugasan, tanggal_mulai, batas_waktu, status_tugas, staf pelaksana, dan daftar rincian unit aset sasaran.")
    add_bullet("d. BastModel: ", "Memetakan id, nomor_bast, jenis_bast, tanggal_bast, pihak_pertama, pihak_kedua, status_bast, hash_dokumen, dan berkas PDF tanda tangan.")

    add_heading_3("2.2.5 Local Persistence Layer (Session & Storage)")
    add_body_p(
        "Lapisan persistensi lokal memanfaatkan pustaka shared_preferences untuk menyimpan data ringan secara persisten di penyimpanan internal perangkat Android (SharedPreferences XML):"
    )
    add_bullet("a. Token Kredensial: ", "Menyimpan nilai token akses Sanctum (key: auth_token) agar pengguna tidak perlu melakukan login ulang setiap kali membuka aplikasi.")
    add_bullet("b. Preferensi Tampilan: ", "Menyimpan konfigurasi mode malam/siang (key: is_dark_mode) dan kustomisasi alamat URL host server API.")

    add_heading_2("2.3 Mekanisme Komunikasi RESTful API dengan Backend Laravel Sanctum")
    add_body_p(
        "Komunikasi antara aplikasi mobile Flutter dan server backend Laravel 12 berlangsung secara stateless melalui protokol HTTPS berformat JSON pada prefix rute /api/v1/. Alur pertukaran datanya adalah sebagai berikut:"
    )
    add_bullet("1. Sesi Login & Penerbitan Token: ", "Pengguna memasukkan username/email dan kata sandi pada LoginScreen. Request dikirim ke POST /api/v1/auth/login. Controller AuthController memverifikasi hash Bcrypt. Jika valid, server Sanctum menerbitkan PlainTextToken baru dan mencatat entitas personal_access_tokens di basis data MySQL. Token dikembalikan ke Flutter dan disimpan pada ApiService dan SharedPreferences.")
    add_bullet("2. Akses Endpoint Terproteksi: ", "Setiap request selanjutnya (misal: GET /api/v1/dashboard atau GET /api/v1/aset) menyertakan Bearer Token pada HTTP Authorization Header. Middleware auth:sanctum di backend memvalidasi token, mengecek status kedaluwarsa, dan mengidentifikasi entitas Pengguna yang sedang aktif.")
    add_bullet("3. Otorisasi Berbasis Peran (RBAC Middleware): ", "Rute-rute administratif seperti penerbitan SPK penugasan (POST /api/v1/penugasan) atau pengesahan BAST diproteksi oleh middleware peran:admin,manajer. Jika peran pengguna tidak memenuhi syarat hierarki, server mengembalikan kode status HTTP 403 Forbidden.")
    add_bullet("4. Transaksi Atomik & Penomoran Dokumen: ", "Saat manajer menerbitkan penugasan atau menyetujui pengadaan, server Laravel menjalankan transaksi database (DB::transaction) dengan penguncian baris (LockForUpdate) pada tabel penomoran_counter guna menjamin nomor dokumen urut tanpa duplikasi.")

    add_heading_2("2.4 Cuplikan Kode Implementasi Arsitektur Terpilih")
    add_body_p(
        "Berikut adalah cuplikan kode program nyata dari repositori proyek yang mengilustrasikan implementasi arsitektur mobile Flutter dan controller API Laravel:"
    )

    code_api_service = (
        "// File: android/lib/core/services/api_service.dart\n"
        "class ApiService {\n"
        "  static final ApiService _instance = ApiService._internal();\n"
        "  factory ApiService() => _instance;\n"
        "  ApiService._internal();\n\n"
        "  String? _token;\n"
        "  Map<String, String> _headers({bool isJson = true}) {\n"
        "    final headers = <String, String>{'Accept': 'application/json'};\n"
        "    if (isJson) headers['Content-Type'] = 'application/json';\n"
        "    if (_token != null && _token!.isNotEmpty) {\n"
        "      headers['Authorization'] = 'Bearer $_token';\n"
        "    }\n"
        "    return headers;\n"
        "  }\n\n"
        "  Future<Map<String, dynamic>> get(String url) async {\n"
        "    try {\n"
        "      final response = await http.get(Uri.parse(url), headers: _headers())\n"
        "          .timeout(const Duration(seconds: 15));\n"
        "      return _processResponse(response);\n"
        "    } on SocketException {\n"
        "      throw 'Tidak dapat terhubung ke server backend. Pastikan Laravel aktif.';\n"
        "    }\n"
        "  }\n"
        "}"
    )
    add_code_block("Klien Jaringan Singleton Ber-Bearer Token (ApiService.dart)", code_api_service, "Dart / Flutter")

    code_penugasan_ctrl = (
        "// File: app/Http/Controllers/Api/PenugasanController.php\n"
        "public function store(Request $request): JsonResponse {\n"
        "    $validated = $request->validate([\n"
        "        'judul_tugas'      => 'required|string|max:200',\n"
        "        'staf_id'          => 'required|exists:pengguna,id',\n"
        "        'batas_waktu'      => 'required|date',\n"
        "        'aset_ids'         => 'required|array|min:1',\n"
        "    ]);\n\n"
        "    return DB::transaction(function () use ($validated, $request) {\n"
        "        $nomor = $this->generateNomorDokumen('TSK');\n"
        "        $penugasan = PenugasanStaf::create([\n"
        "            'nomor_penugasan' => $nomor,\n"
        "            'judul_tugas'     => $validated['judul_tugas'],\n"
        "            'staf_id'         => $validated['staf_id'],\n"
        "            'pemberi_tugas_id'=> $request->user()->id,\n"
        "            'status_tugas'    => 'belum_mulai',\n"
        "        ]);\n"
        "        // Insert detail checklist aset\n"
        "        foreach ($validated['aset_ids'] as $asetId) {\n"
        "            DetailPenugasan::create([\n"
        "                'penugasan_staf_id' => $penugasan->id,\n"
        "                'aset_id'           => $asetId,\n"
        "            ]);\n"
        "        }\n"
        "        return response()->json(['status' => 'success', 'data' => $penugasan], 201);\n"
        "    });\n"
        "}"
    )
    add_code_block("API Controller Pembuatan SPK & Checklist (PenugasanController.php)", code_penugasan_ctrl, "PHP / Laravel 12")

    doc.add_page_break()

    # ==========================================
    # BAB III: DESAIN USER INTERFACE APLIKASI MOBILE (FIGMA)
    # ==========================================
    add_heading_1("BAB III DESAIN USER INTERFACE APLIKASI MOBILE (FIGMA)")

    add_heading_2("3.1 Sistem Desain Visual (Design System Tokens)")
    add_body_p(
        "Perancangan antarmuka pengguna (User Interface) aplikasi SI-ASET dibangun secara presisi menggunakan perangkat lunak desain industri Figma / Adobe XD dengan mematuhi pedoman Material Design 3 (Material You). Sistem desain diformulasikan ke dalam token desain visual terpadu untuk memastikan konsistensi tampilan antarhalaman, kenyamanan membaca data teknis, dan keindahan estetika korporat modern."
    )

    add_heading_3("3.1.1 Tipografi dan Skala Hierarki Teks (Plus Jakarta Sans)")
    add_body_p(
        "Kelompok kami memilih keluarga huruf Plus Jakarta Sans (diintegrasikan melalui pustaka google_fonts). Karakter geometris sans-serif yang modern, bersih, dan memiliki keterbacaan (legibility) sangat tinggi pada layar smartphone beresolusi tinggi maupun rendah. Skala hierarki teks yang diterapkan adalah:"
    )
    add_bullet("Headline Large (28sp / Bold): ", "Digunakan untuk sapaan pembuka login dan header angka saldo aset.")
    add_bullet("Headline Medium (22sp / Bold): ", "Digunakan untuk judul kartu statistik utama pada dashboard.")
    add_bullet("Title Large (18sp / Semi-Bold): ", "Digunakan untuk judul AppBar halaman dan nama modal dialog.")
    add_bullet("Title Medium (15sp / Semi-Bold): ", "Digunakan untuk label section form dan judul kartu penugasan.")
    add_bullet("Body Large (14sp / Medium): ", "Digunakan untuk teks masukan form, nama item aset, dan konten tabel.")
    add_bullet("Body Medium (13sp / Regular): ", "Digunakan untuk deskripsi penugasan, nomor inventaris, dan catatan temuan.")
    add_bullet("Label Small / Caption (11sp / Bold): ", "Digunakan untuk teks chip status badge, badge lonceng, dan stempel waktu.")

    add_heading_3("3.1.2 Palet Warna Korporat (Navy Blue & Vibrant Orange)")
    add_body_p(
        "Palet warna dirancang dengan kontras tajam yang menggabungkan kedalaman warna Navy Blue (merepresentasikan profesionalisme, stabilitas, dan keamanan data enterprise) dengan aksen Vibrant Orange (memberikan energi visual, menyoroti aksi primer/CTA, dan mengarahkan fokus pengguna). Nilai heksadesimal warna yang digunakan adalah:"
    )
    add_bullet("Navy Darkest (#05162A) & Dark (#0B2545): ", "Warna latar belakang Dark Mode dan kontainer AppBar elegan.")
    add_bullet("Navy Primary (#13315C) & Light (#134074): ", "Warna identitas korporat utama untuk teks header, judul kartu, dan batas kartu.")
    add_bullet("Vibrant Orange Primary (#FF7A00): ", "Warna aksen utama untuk tombol primer (CTA), active tab indicator, dan highlight.")
    add_bullet("Status Semantic Colors: ", "Success (#00A86B / Hijau Zamrud), Warning (#FF9F1C / Kuning Kunyit), Danger (#E63946 / Merah Crimson), dan Info (#2A9D8F / Toska).")
    add_bullet("Backgrounds: ", "Light Background (#F8FAFC) dan Card Light (#FFFFFF) dengan elevasi bayangan halus (shadow blur 16dp, alpha 0.06).")

    add_heading_3("3.1.3 Komponen Interaktif (Header, Menu, Button, Toggle, Form)")
    add_body_p(
        "Seluruh komponen antarmuka dirancang modular dalam library komponen Figma / Adobe XD:"
    )
    add_bullet("a. Dynamic Header (AppBar): ", "Menampilkan logo teks SI-ASET berwarna oranye, nama kantor aktif penempatan staf, avatar foto pengguna, dan ikon lonceng notifikasi dengan counter merah.")
    add_bullet("b. Bottom Navigation Bar (Menu Utama): ", "5 tab terstruktur (Beranda, Aset, Scan, Operasional, Laporan) dengan indikator aktif pil oranye bulat dan shadow lembut.")
    add_bullet("c. Form Controls: ", "Text field melengkung dengan border radius 12dp, dropdown filter pencarian, date picker kalender pop-up, serta area unggah gambar kamera.")
    add_bullet("d. Buttons: ", "Elevated button oranye tebal untuk aksi simpan/submit, outlined button navy untuk pembatalan, dan Floating Action Button (FAB) oranye.")
    add_bullet("e. Toggles: ", "Switch toggle Dark/Light mode pada pengaturan profil dan toggle lampu senter flash kamera pada modul scanning.")
    add_bullet("f. Touch Signature Canvas: ", "Komponen kanvas sentuh khusus untuk tanda tangan digital pada Berita Acara Serah Terima (BAST).")

    add_heading_2("3.2 Katalog Penanda Tampilan UI Mobile Apps (Figma / Adobe XD)")
    add_body_p(
        "Berikut adalah katalog penanda visual desain antarmuka aplikasi mobile SI-ASET yang dirancang pada Figma / Adobe XD, lengkap dengan deskripsi fungsional dan spesifikasi komponen interaktifnya:"
    )

    # 1. SCREENSHOT PLACEHOLDER 1: LOGIN
    add_screenshot_placeholder(
        "Gambar 3.1",
        "Desain Antarmuka Layar Autentikasi / Login Screen",
        "Login Screen",
        ["GlassCard Container", "Header Logo SI-ASET", "Input Form Email/Username", "Input Form Kata Sandi (Password Obscure Toggle)", "Primary Elevated Button (Masuk)", "Role Demo Selector Chips", "Base URL Server Configuration Dialog"],
        "Layar login gerbang autentikasi mobile. Memuat form kredensial aman, tombol login oranye pekat, pemilih cepat akun demo untuk 4 peran pengguna (Admin, Kepala Kantor, Manajer, Staf), serta tombol pengaturan host URL server backend API."
    )

    # 2. SCREENSHOT PLACEHOLDER 2: DASHBOARD
    add_screenshot_placeholder(
        "Gambar 3.2",
        "Desain Antarmuka Layar Dashboard Utama (Beranda)",
        "Dashboard Screen",
        ["Dynamic Top Header & Notification Bell Badge", "Welcome User Card", "Statistik Ringkasan Kartu Metrik (Total Aset, Jadwal Servis, SPK Aktif)", "Shortcut Quick Actions Grid", "Daftar Tugas Mendesak", "Bottom Navigation Bar 5 Menu"],
        "Layar beranda adaptif berbasis peran. Menampilkan ringkasan metrik total nilai aset, jumlah tiket servis yang jatuh tempo, daftar tugas lapangan staf hari ini, serta akses pintas ke fitur-fitur operasional utama."
    )

    # 3. SCREENSHOT PLACEHOLDER 3: MASTER ASET
    add_screenshot_placeholder(
        "Gambar 3.3",
        "Desain Antarmuka Layar Katalog & Daftar Master Aset",
        "Aset List Screen",
        ["Header Pencarian Instan (Instant Search Bar)", "Horizontal Filter Chip Kategori (Semua, IT, Kendaraan, Mesin)", "Card Item Aset Ber-Border Halus", "CustomBadge Kondisi (Sangat Baik / Baik / Rusak)", "Label Nomor Inventaris Monospace", "Floating Action Button (Tambah Aset)"],
        "Layar inventaris master aset. Menyajikan daftar aset perusahaan dengan pencarian real-time berdasarkan nomor inventaris atau nama barang, filter kategori dinamis, dan visualisasi status kondisi fisik secara transparan."
    )

    # 4. SCREENSHOT PLACEHOLDER 4: DETAIL ASET
    add_screenshot_placeholder(
        "Gambar 3.4",
        "Desain Antarmuka Layar Detail Aset & Riwayat Penempatan",
        "Aset Detail Screen",
        ["Header Image Slider Foto Fisik Aset", "Widget QR Code Otomatis (qr_flutter)", "Spesifikasi Teknis Lengkap (Merk, Serial Number, Tipe)", "Informasi Finansial (Harga Perolehan, Masa Manfaat, Depresiasi)", "Timeline Riwayat Mutasi Penempatan", "Tombol Aksi Servis / Penugasan"],
        "Layar detail aset mendalam. Menampilkan foto resolusi tinggi, kode QR siap cetak, rincian teknis, lokasi penempatan kantor/divisi aktif, penanggung jawab barang, serta jejak riwayat mutasi aset sejak penerimaan."
    )

    # 5. SCREENSHOT PLACEHOLDER 5: SCANNER QR
    add_screenshot_placeholder(
        "Gambar 3.5",
        "Desain Antarmuka Layar Pemindai Kamera Barcode & QR Code",
        "Scan Screen",
        ["Full-Screen Camera Viewport (mobile_scanner)", "Animated Scan Target Overlay (Crosshair Box)", "Toggle Switch Lampu Senter Flash (Torch On/Off)", "Camera Facing Switcher", "Bottom Sheet Input Manual Nomor Inventaris", "Dialog Peringatan Aset Tidak Ditemukan"],
        "Layar pemindai kamera perangkat keras. Mampu memindai kode QR dan barcode Code 128 dengan kecepatan decoding di bawah 500 ms, dilengkapi kontrol lampu flash untuk area minim cahaya dan opsi ketik manual jika label rusak."
    )

    # 6. SCREENSHOT PLACEHOLDER 6: FORM SPK PENUGASAN
    add_screenshot_placeholder(
        "Gambar 3.6",
        "Desain Antarmuka Formulir Penerbitan Penugasan / SPK Kerja",
        "Penugasan Form Screen",
        ["Header AppBar 'Buat SPK Penugasan'", "Input Text Field Judul & Instruksi Tugas", "Dropdown Selector Staf Teknisi Pelaksana", "Dropdown Selector Jenis Tugas (Opname, Servis, Penarikan, Pelabelan)", "Date Picker Batas Waktu (Tenggat Penyelesaian)", "Multi-Select Asset Dialog with Checkboxes", "Tombol Primary Submit SPK"],
        "Layar formulir manajer untuk menerbitkan Surat Perintah Kerja (SPK). Memungkinkan penentuan staf pelaksana, penetapan deadline, pemilihan multi-unit aset target audit, serta penulisan petunjuk teknis pelaksanaan."
    )

    # 7. SCREENSHOT PLACEHOLDER 7: DETAIL & CHECKLIST PENUGASAN
    add_screenshot_placeholder(
        "Gambar 3.7",
        "Desain Antarmuka Pelaksanaan Tugas Lapangan & Checklist Fisik",
        "Penugasan Detail Screen",
        ["Status Banner Tugas (Belum Mulai / Dikerjakan / Validasi)", "Daftar Checklist Unit Aset yang Harus Diperiksa", "Tombol Kamera Ambil Foto Bukti Lapangan (image_picker)", "Input Form Catatan Temuan Kondisi Fisik", "Dropdown Update Kondisi Pasca Pekerjaan", "Tombol Kirim Laporan ke Manajer"],
        "Layar kerja staf teknisi di lapangan. Menampilkan daftar aset yang ditugaskan, tombol ambil foto kondisi fisik aktual di tempat, verifikasi barcode fisik, dan form pelaporan penyelesaian pekerjaan."
    )

    # 8. SCREENSHOT PLACEHOLDER 8: VALIDASI MANAJER
    add_screenshot_placeholder(
        "Gambar 3.8",
        "Desain Antarmuka Layar Validasi Hasil Penugasan oleh Manajer",
        "Penugasan Validation Screen",
        ["Ringkasan Hasil Kerja Staf", "Galeri Foto Bukti Temuan Lapangan", "Input Catatan Review / Feedback Manajer", "Tombol Aksi Tolak / Minta Revisi (Warna Merah)", "Tombol Aksi Setujui & Validasi Selesai (Warna Hijau)", "Dialog Konfirmasi Putusan"],
        "Layar validasi manajer untuk memeriksa hasil pengerjaan staf. Manajer dapat memverifikasi foto bukti fisik dan memutuskan apakah hasil kerja disetujui atau dikembalikan untuk revisi lapangan."
    )

    # 9. SCREENSHOT PLACEHOLDER 9: BAST & TANDA TANGAN ELEKTRONIK
    add_screenshot_placeholder(
        "Gambar 3.9",
        "Desain Antarmuka Berita Acara Serah Terima (BAST) & Kanvas TTD",
        "BAST Detail & Signature Screen",
        ["Header Dokumen Legalitas Nomor BAST", "Informasi Pihak Pertama (Menyerahkan) & Pihak Kedua (Menerima)", "Daftar Aset yang Diserahterimakan", "Interactive Touch Signature Canvas", "Tombol Bersihkan Goresan (Clear Canvas)", "Tombol Sahkan & Tanda Tangani BAST", "Tombol Cetak / Unduh Dokumen PDF Ber-Hash SHA-256"],
        "Layar legalitas Berita Acara Serah Terima (BAST). Dilengkapi kanvas sentuh untuk penandatanganan elektronik langsung oleh pemegang aset dan manajer, pembentukan hash integritas SHA-256, serta ekspor dokumen PDF resmi."
    )

    # 10. SCREENSHOT PLACEHOLDER 10: OPERASIONAL HUB
    add_screenshot_placeholder(
        "Gambar 3.10",
        "Desain Antarmuka Hub Operasional (Servis, Pengadaan, Sewa)",
        "Operasional Hub Screen",
        ["Tab Bar Segmented (Jadwal Pemeliharaan, Tiket Servis, Pengadaan Aset, Kontrak Sewa Vendor)", "Kartu Peringatan Jatuh Tempo Servis Berkala", "Tombol Pengajuan Pengadaan Barang Baru (Biaya Kantor)", "Status Workflow Persetujuan Kepala Kantor", "Daftar Kontrak Sewa Leasing Aktif"],
        "Layar pusat operasional yang menggabungkan modul pemeliharaan preventif, tiket perbaikan teknis mendadak, pengajuan pengadaan barang baru beserta justifikasi biaya, dan pemantauan masa sewa vendor."
    )

    # 11. SCREENSHOT PLACEHOLDER 11: LAPORAN & DEPRESIASI
    add_screenshot_placeholder(
        "Gambar 3.11",
        "Desain Antarmuka Layar Laporan & Statistik Depresiasi Aset",
        "Laporan & Depresiasi Screen",
        ["Header Ringkasan Nilai Kapitalisasi Aset & Akumulasi Penyusutan", "Dropdown Filter Periode Buku (Bulan & Tahun)", "Tabel Buku Besar Penyusutan (Metode Garis Lurus & Saldo Menurun)", "Daftar Rekomendasi Peremajaan Aset", "Tombol Ekspor Laporan PDF Cetak", "Dropdown Pemilihan Kantor & Divisi"],
        "Layar pelaporan eksekutif dan akuntansi. Menyajikan perhitungan nilai buku aset bulanan, daftar barang yang telah habis masa manfaat ekonomisnya, serta tombol ekspor laporan formal berformat PDF."
    )

    # 12. SCREENSHOT PLACEHOLDER 12: NOTIFIKASI & PROFIL
    add_screenshot_placeholder(
        "Gambar 3.12",
        "Desain Antarmuka Layar Notifikasi In-App & Profil Pengguna",
        "Notifikasi & Profil Screen",
        ["Daftar Notifikasi Belum Dibaca (Tugas Baru, Jadwal Servis, Persetujuan)", "Avatar Profil Pengguna & Nomor Induk Karyawan (NIK)", "Informasi Jabatan, Kantor, dan Divisi Penempatan", "Toggle Switch Mode Gelap / Terang (Dark Mode)", "Tombol Ubah Kata Sandi Akun", "Tombol Keluar Akun (Logout)"],
        "Layar pusat informasi pengguna. Memuat feed notifikasi kegiatan terkini, detail identitas akun staf, toggle pergantian tema visual gelap/terang, dan pengaturan keamanan kredensial."
    )

    doc.add_page_break()

    # ==========================================
    # BAB IV: KONSEP DESAIN USER EXPERIENCE (UX)
    # ==========================================
    add_heading_1("BAB IV KONSEP DESAIN USER EXPERIENCE (UX) APLIKASI MOBILE")

    add_heading_2("4.1 Filosofi User-Centered Design (UCD) pada Lingkungan Kerja Fisik")
    add_body_p(
        "Perancangan User Experience (UX) pada aplikasi mobile SI-ASET berakar pada filosofi User-Centered Design (UCD). Pendekatan ini menempatkan kondisi kerja nyata para staf teknisi di lapangan dan manajer operasional sebagai pusat dari seluruh keputusan desain antarmuka."
    )
    add_body_p(
        "Berdasarkan hasil observasi konteks penggunaan, staf teknisi di lapangan sering kali harus memeriksa aset fisik di lingkungan yang penuh tantangan: lorong gudang penyimpanan logistik yang minim cahaya, ruang server bertemperatur dingin dengan kabel berseliweran, atau berpindah antar-lantai gedung perkantoran sambil membawa perangkat kerja. Dalam kondisi tersebut, staf umumnya hanya dapat mengoperasikan smartphone dengan satu tangan (single-hand grip) sementara tangan lainnya memegang peralatan, memindahkan boks aset, atau memeriksa label barcode fisik."
    )
    add_body_p(
        "Oleh karena itu, prinsip desain UX SI-ASET dirancang untuk menghilangkan friksi kognitif, mempercepat alur kerja pemindaian data, mencegah kesalahan input data di tempat, serta menjamin kejelasan informasi dalam situasi apa pun."
    )

    add_heading_2("4.2 Penerapan 10 Prinsip Heuristik Usability Jakob Nielsen")
    add_body_p(
        "Untuk memastikan standar kualitas interaksi manusia dan komputer yang unggul, rancangan UX SI-ASET dievaluasi secara komprehensif mengacu pada 10 Prinsip Heuristik Jakob Nielsen:"
    )
    add_bullet("1. Visibility of System Status: ", "Sistem selalu menginformasikan status terkini kepada pengguna. Ketika aplikasi memproses request API, indikator loading spinner melingkar ditampilkan. Setiap status dokumen aset dan penugasan diberi label warna semantik (Hijau = Selesai, Kuning = Dalam Proses, Merah = Ditolak/Revisi). Ikon lonceng notifikasi dilengkapi badge angka dinamis yang menunjukkan jumlah pemberitahuan belum dibaca.")
    add_bullet("2. Match Between System and the Real World: ", "Seluruh istilah, terminologi, dan alur kerja di dalam aplikasi menggunakan bahasa Indonesia formal yang lazim digunakan dalam administrasi aset perusahaan (seperti Surat Perintah Kerja [SPK], Berita Acara Serah Terima [BAST], Stock Opname, Beban Penyusutan, dan Peremajaan Aset), bukan menggunakan istilah teknis basis data yang membingungkan.")
    add_bullet("3. User Control and Freedom: ", "Setiap formulir pop-up dan modal dialog dilengkapi tombol 'Batal' yang jelas. Jika pengguna tidak sengaja membuka kamera scanner atau formulir penugasan, mereka dapat kembali ke halaman sebelumnya dengan mudah melalui tombol panah kembali di AppBar maupun gestur back Android standar.")
    add_bullet("4. Consistency and Standards: ", "Penerapan Material Design 3 menjamin konsistensi pola interaksi di seluruh aplikasi. Elemen tombol primer selalu berwarna oranye pekat dengan border radius 12dp, warna teks header selalu konsisten mengacu pada palet Navy Blue, dan posisi navigasi utama selalu berada pada Bottom Navigation Bar.")
    add_bullet("5. Error Prevention (Pencegahan Kesalahan): ", "Sistem menerapkan validasi input yang ketat sebelum data dikirim ke server. Tombol 'Kirim Laporan' pada pengerjaan tugas lapangan dinonaktifkan (disabled) secara otomatis apabila staf belum melampirkan foto bukti kondisi fisik. Pada proses krusial seperti penolakan SPK atau finalisasi BAST, sistem selalu memunculkan modal dialog konfirmasi konfirmasi ganda.")
    add_bullet("6. Recognition Rather than Recall: ", "Aplikasi meminimalkan beban ingatan pengguna dengan menyajikan data aset dalam bentuk kartu informasi visual yang kaya (memuat gambar, merk, nomor seri, dan lokasi rak). Saat membuat penugasan, manajer dapat memilih unit aset langsung dari daftar pencarian modal tanpa harus mengetik manual kode inventaris yang panjang.")
    add_bullet("7. Flexibility and Efficiency of Use: ", "Aplikasi menyediakan jalur pintas (accelerators) bagi staf berpengalaman, seperti pemindaian barcode kamera otomatis yang langsung membuka profil aset tanpa perlu mengetik, filter cepat berdasarkan kategori aset di halaman utama, serta tombol pemilih cepat akun demo pada halaman login.")
    add_bullet("8. Aesthetic and Minimalist Design: ", "Tata letak kartu mengadopsi gaya modern Glassmorphism dengan ruang bernapas (whitespace) yang cukup, menghindari cluttering informasi yang tidak relevan. Data teknis yang panjang diringkas ke dalam tab dan section yang dapat dibuka-tutup.")
    add_bullet("9. Help Users Recognize, Diagnose, and Recover from Errors: ", "Pesan kesalahan disajikan dalam bahasa yang santun, jelas, dan solutif. Contoh: apabila label QR code rusak atau tidak terbaca, aplikasi tidak menampilkan error code teknis, melainkan dialog ramah: 'Aset tidak ditemukan. Pastikan pencahayaan cukup atau gunakan fitur pencarian manual'.")
    add_bullet("10. Help and Documentation: ", "Setiap field formulir yang memerlukan format khusus dilengkapi placeholder hint deskriptif (misal: 'Contoh: Lantai 3 Ruang Server Rak A-02' pada lokasi penempatan).")

    add_heading_2("4.3 Alur Pengalaman Pengguna (User Journey & Task Flows)")
    add_body_p(
        "Untuk menggambarkan keterpaduan interaksi antar-pengguna, kelompok kami merumuskan dua alur perjalanan pengguna (User Journey) inti pada operasional harian:"
    )

    add_heading_3("4.3.1 User Journey Staf Teknisi (Inspeksi & Stock Opname Lapangan)")
    add_body_p(
        "Alur kerja staf teknisi dirancang seringkas mungkin untuk memaksimalkan produktivitas fisik:"
    )
    add_bullet("Langkah 1 (Notifikasi Masuk): ", "Staf menerima pemberitahuan tugas SPK baru di ikon lonceng dashboard, membuka notifikasi, dan melihat daftar lokasi serta unit aset sasaran audit.")
    add_bullet("Langkah 2 (Menuju Lokasi & Membuka Scan): ", "Staf mendatangi ruangan target, menekan tombol navigasi 'Scan' di bagian tengah Bottom Bar. Kamera aktif seketika dengan target overlay crosshair.")
    add_bullet("Langkah 3 (Pemindaian Barcode / QR): ", "Staf mengarahkan kamera ke label stiker aset. Dalam waktu kurang dari 500 ms, terdengar haptic vibration dan layar otomatis membuka halaman verifikasi aset.")
    add_bullet("Langkah 4 (Checklist & Foto Bukti): ", "Staf memeriksa kondisi fisik barang, mencentang checklist pemeriksaan, mengetik catatan temuan (jika ada cacat), dan menekan tombol kamera untuk mengambil foto fisik unit aset secara wajib.")
    add_bullet("Langkah 5 (Kirim Laporan): ", "Staf menekan tombol 'Kirim Hasil Pekerjaan'. Status penugasan otomatis berubah menjadi 'Menunggu Validasi Manajer' dan sistem mengirim notifikasi ke manajer.")

    add_heading_3("4.3.2 User Journey Manajer (Validasi Pekerjaan & Pengesahan BAST)")
    add_body_p(
        "Alur kerja manajer menitikberatkan pada pengawasan mutu, kepatuhan audit, dan legalitas dokumen:"
    )
    add_bullet("Langkah 1 (Review Laporan Staf): ", "Manajer menerima notifikasi bahwa staf telah menyelesaikan SPK. Manajer membuka detail tugas dan memeriksa galeri foto bukti kondisi fisik yang diunggah staf.")
    add_bullet("Langkah 2 (Keputusan Validasi): ", "Jika foto dan kondisi sesuai, manajer menekan 'Setujui' sehingga status tugas menjadi 'Selesai' dan kondisi master aset otomatis diperbarui. Jika tidak sesuai, manajer menekan 'Tolak' disertai catatan perbaikan.")
    add_bullet("Langkah 3 (Pengesahan BAST & E-Signature): ", "Untuk serah terima aset baru atau mutasi, manajer membuka dokumen BAST, membubuhkan tanda tangan elektronik di atas kanvas sentuh smartphone, dan menekan 'Sahkan Dokumen'.")
    add_bullet("Langkah 4 (Pencetakan Dokumen Resmi): ", "Sistem membentuk hash integritas SHA-256 dan menyematkan tanda tangan ke dalam berkas PDF BAST resmi. Manajer dapat langsung mencetak dokumen ke printer jaringan nirkabel via tombol 'Cetak PDF'.")

    add_heading_2("4.4 Desain Ergonomi Jempol (Thumb-Zone) dan Pencegahan Kesalahan (Poka-Yoke)")
    add_body_p(
        "Kenyamanan fisik pengguna menjadi prioritas mutlak dalam perancangan mobile. Mengacu pada penelitian ergonomi antarmuka sentuh (Steven Hoober, 'Designing for Touch'), layar smartphone terbagi ke dalam tiga zona jangkauan jempol: Natural Zone (area bawah yang sangat mudah dijangkau), Stretch Zone (area tengah yang memerlukan peregangan jari), dan Hard-to-Reach Zone (area atas yang sulit dijangkau satu tangan)."
    )
    add_body_p(
        "Implementasi ergonomi jempol pada SI-ASET meliputi:\n"
        "1. Penempatan Menu Kritis di Natural Zone: Seluruh navigasi utama (Bottom Navigation Bar), tombol 'Scan Barcode', tombol 'Simpan/Submit Form', dan Floating Action Button (FAB) diposisikan di 35% area bawah layar.\n"
        "2. Ukuran Touch Target Ergonomis: Seluruh tombol interaktif, item list, dan ikon menu memiliki dimensi sentuh minimal 48 x 48 density-independent pixels (dp) dengan padding yang memadai, mencegah kesalahan sentuh (fat-finger syndrome).\n"
        "3. Penerapan Konsep Poka-Yoke (Mistake Proofing): Sistem mencegah kelalaian manusia dengan aturan validasi ketat:\n"
        "   - Wajib Unggah Foto: Tombol kirim laporan terkunci jika bukti foto kamera belum diambil.\n"
        "   - Konfirmasi Aksi Destruktif: Tindakan penolakan SPK dan pembatalan kontrak sewa mewajibkan pengisian alasan teks dan konfirmasi dialog ganda.\n"
        "   - Atomic Counter: Nomor dokumen digenerate otomatis di tingkat basis data, menghilangkan celah nomor ganda akibat input manual staf."
    )

    add_heading_2("4.5 Standar Aksesibilitas WCAG 2.1 AA dan Optimasi Lingkungan Nyata")
    add_body_p(
        "Aplikasi SI-ASET memenuhi standar aksesibilitas internasional Web Content Accessibility Guidelines (WCAG 2.1) Level AA dengan fokus pada:"
    )
    add_bullet("a. Rasio Kontras Warna Teks (NFR-10): ", "Seluruh teks konten utama memiliki rasio kontras terhadap warna latar belakang minimal 4.5:1 untuk teks standar dan minimal 3.0:1 untuk teks berukuran besar (bold 18sp+). Pada Mode Terang (Light Mode), teks Navy Darkest (#05162A) di atas latar #F8FAFC menghasilkan rasio kontras sangat tinggi (> 12:1). Pada Mode Gelap (Dark Mode), teks putih di atas latar #07192F menghasilkan kontras > 14:1.")
    add_bullet("b. Fitur Dark Mode Terintegrasi: ", "Menyediakan toggle mode gelap yang mudah diakses pada menu Profil. Mode gelap sangat bermanfaat untuk mengurangi kelelahan mata staf teknisi saat bertugas di ruang server yang remang-remang serta menghemat konsumsi daya baterai perangkat berlayar AMOLED hingga 30%.")
    add_bullet("c. Fitur Senter Flash (Torch Support): ", "Modul scanner kamera dilengkapi tombol toggle cepat untuk menyalakan lampu kilat LED smartphone, memudahkan pembacaan barcode pada aset yang tersimpan di kolong meja atau rak gudang yang gelap.")

    doc.add_page_break()

    # ==========================================
    # BAB V: KESIMPULAN DAN SARAN PENGEMBANGAN
    # ==========================================
    add_heading_1("BAB V KESIMPULAN DAN SARAN PENGEMBANGAN")

    add_heading_2("5.1 Kesimpulan")
    add_body_p(
        "Berdasarkan perancangan dan implementasi lanjutan yang telah diselesaikan oleh Kelompok 1 pada proyek aplikasi SI-ASET (Sistem Informasi Manajemen Aset Perusahaan), dapat ditarik beberapa kesimpulan utama sebagai berikut:"
    )
    add_bullet("1. Penerapan Metodologi Mobile-D yang Tepat: ", "Metodologi Mobile-D terbukti sangat efektif dan relevan dalam mengawal siklus hidup pengembangan aplikasi mobile SI-ASET. Pembagian ke dalam 5 fase terstruktur (Explore, Initialize, Production, System Test, dan Fixize) berhasil menjaga ritme kerja kelompok mahasiswa secara tangkas, memfasilitasi iterasi fitur secara modular, dan menjamin integrasi yang mulus antara klien mobile Android Flutter dan backend RESTful API Laravel 12.")
    add_bullet("2. Kestabilan Arsitektur Berbasis Clean Architecture & MVVM: ", "Arsitektur aplikasi mobile yang dirancang dengan memadukan Layered Clean Architecture dan Model-View-ViewModel (MVVM) menggunakan state management Provider menghasilkan struktur kode program yang sangat bersih, modular, dan terisolasi. Logika jaringan pada ApiService singleton, manajemen sesi pada AuthProvider, serta logika antarmuka pada Presentation Layer bekerja secara harmonis tanpa saling tumpang tindih.")
    add_bullet("3. Desain Antarmuka (UI) Modern dan Komprehensif: ", "Perancangan desain antarmuka berbasis Figma / Adobe XD telah mencakup seluruh kebutuhan studi kasus inventarisasi perusahaan. Sistem desain berbasis Plus Jakarta Sans dan palet warna korporat Navy-Orange menghasilkan visual yang profesional, rapi, dan konsisten di seluruh 12 layar antarmuka utama (mulai dari login, dashboard adaptif per role, scanner kamera, form SPK penugasan, hingga kanvas tanda tangan BAST).")
    add_bullet("4. Pengalaman Pengguna (UX) yang Berpusat pada Pengguna: ", "Konsep UX yang berlandaskan 10 Prinsip Heuristik Jakob Nielsen, penataan ergonomi jempol (Thumb-Zone), sistem pencegahan kesalahan (Poka-Yoke), serta pemenuhan standar aksesibilitas WCAG 2.1 AA terbukti memberikan kemudahan, kecepatan, dan kenyamanan operasional yang optimal bagi staf teknisi di lapangan maupun manajer di kantor.")

    add_heading_2("5.2 Saran Pengembangan Lanjutan")
    add_body_p(
        "Meskipun aplikasi SI-ASET telah memiliki fondasi rancangan arsitektur, antarmuka, dan alur kerja yang sangat kokoh, beberapa aspek pengembangan berikut dapat diterapkan pada iterasi riset berikutnya:"
    )
    add_bullet("1. Implementasi Local Database & Offline Synchronization: ", "Menambahkan basis data lokal pada perangkat Android (seperti SQLite atau Isar Database) yang dipadukan dengan background sync service. Hal ini memungkinkan staf teknisi tetap dapat melakukan stock opname dan inspeksi aset di area gudang basement yang tidak terjangkau sinyal internet, dan data akan otomatis tersinkronisasi ke server pusat saat kembali online.")
    add_bullet("2. Integrasi Teknologi RFID dan Bluetooth Low Energy (BLE): ", "Mengembangkan dukungan pemindaian nirkabel menggunakan RFID Reader portabel atau BLE Beacon untuk aset berharga tinggi (high-value assets), sehingga proses pelacakan keberadaan aset di suatu ruangan dapat dilakukan secara otomatis tanpa harus memindai barcode satu per satu.")
    add_bullet("3. Integrasi Push Notification Firebase Cloud Messaging (FCM): ", "Mengintegrasikan layanan notifikasi push berbasis cloud dari Google Firebase agar pemberitahuan SPK penugasan baru dan alarm jatuh tempo servis dapat muncul langsung pada status bar Android meskipun aplikasi sedang ditutup.")
    add_bullet("4. Verifikasi Biometrik (Fingerprint & Face ID): ", "Menambahkan opsi autentikasi biometrik pada proses penandatanganan elektronik BAST untuk meningkatkan aspek non-repudiation dan kekuatan legalitas hukum dokumen.")

    doc.add_page_break()

    # ==========================================
    # DAFTAR PUSTAKA
    # ==========================================
    add_heading_1("DAFTAR PUSTAKA")

    daftar_pustaka = [
        "Abrahamsson, P., Hanhineva, A., Hulkko, H., Ihme, T., Jäälinoja, J., & Salo, O. (2004). Mobile-D: An agile approach for mobile software development. Companion to the 19th Annual ACM SIGPLAN Conference on Object-Oriented Programming Systems, Languages, and Applications (OOPSLA '04), 174-175. ACM.",
        "Booch, G., Rumbaugh, J., & Jacobson, I. (2005). The unified modeling language user guide (2nd ed.). Addison-Wesley.",
        "Dennis, A., Wixom, B. H., & Tegarden, D. (2015). Systems analysis and design: An object-oriented approach with UML (5th ed.). John Wiley & Sons.",
        "Flutter Documentation. (2024). Architectural overview and state management with Provider. Google LLC. https://docs.flutter.dev/",
        "Google. (2023). Material Design 3 (Material You) specifications. Google Design. https://m3.material.io/",
        "Hoober, S. (2017). How do users really hold mobile devices? Designing for touch and ergonomics. UXmatters Magazine.",
        "Laravel Documentation. (2024). Laravel 12.x RESTful API, Sanctum authentication, and Eloquent ORM. Laravel LLC. https://laravel.com/docs/12.x",
        "Martin, R. C. (2018). Clean architecture: A craftsman's guide to software structure and design. Prentice Hall.",
        "Nielsen, J. (1994). 10 usability heuristics for user interface design. Nielsen Norman Group. https://www.nngroup.com/articles/ten-usability-heuristics/",
        "Pressman, R. S., & Maxim, B. R. (2020). Software engineering: A practitioner's approach (9th ed.). McGraw-Hill Education.",
        "Sommerville, I. (2016). Software engineering (10th ed.). Pearson Education.",
        "W3C. (2018). Web Content Accessibility Guidelines (WCAG) 2.1. World Wide Web Consortium. https://www.w3.org/TR/WCAG21/"
    ]

    for pustaka in daftar_pustaka:
        p = doc.add_paragraph()
        p.paragraph_format.first_line_indent = Inches(-0.5)
        p.paragraph_format.left_indent = Inches(0.5)
        p.paragraph_format.space_before = Pt(3)
        p.paragraph_format.space_after = Pt(6)
        p.paragraph_format.line_spacing = 1.5
        run = p.add_run(pustaka)
        run.font.name = 'Times New Roman'
        run.font.size = Pt(11)
        run.font.color.rgb = DARK_TEXT

    # Save to Root and android directory
    output_filename = "Laporan_Pengembangan_Mobile_Apps_SI-ASET.docx"
    doc.save(output_filename)
    print(f"Document saved successfully as: {output_filename}")

    android_path = os.path.join("android", output_filename)
    doc.save(android_path)
    print(f"Document copy saved to: {android_path}")

if __name__ == "__main__":
    create_document()
