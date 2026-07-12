import json
import sys
from pathlib import Path

import openpyxl


COMMUNITIES = {
    "01": "Andalucía", "02": "Aragón", "03": "Asturias",
    "04": "Illes Balears", "05": "Canarias", "06": "Cantabria",
    "07": "Castilla y León", "08": "Castilla-La Mancha",
    "09": "Cataluña", "10": "Comunitat Valenciana",
    "11": "Extremadura", "12": "Galicia", "13": "Comunidad de Madrid",
    "14": "Región de Murcia", "15": "Comunidad Foral de Navarra",
    "16": "País Vasco", "17": "La Rioja",
    "18": "Ciudad Autónoma de Ceuta", "19": "Ciudad Autónoma de Melilla",
}

PROVINCES = {
    "01": "Araba/Álava", "02": "Albacete", "03": "Alicante/Alacant",
    "04": "Almería", "05": "Ávila", "06": "Badajoz", "07": "Illes Balears",
    "08": "Barcelona", "09": "Burgos", "10": "Cáceres", "11": "Cádiz",
    "12": "Castellón/Castelló", "13": "Ciudad Real", "14": "Córdoba",
    "15": "A Coruña", "16": "Cuenca", "17": "Girona", "18": "Granada",
    "19": "Guadalajara", "20": "Gipuzkoa", "21": "Huelva", "22": "Huesca",
    "23": "Jaén", "24": "León", "25": "Lleida", "26": "La Rioja",
    "27": "Lugo", "28": "Madrid", "29": "Málaga", "30": "Murcia",
    "31": "Navarra", "32": "Ourense", "33": "Asturias", "34": "Palencia",
    "35": "Las Palmas", "36": "Pontevedra", "37": "Salamanca",
    "38": "Santa Cruz de Tenerife", "39": "Cantabria", "40": "Segovia",
    "41": "Sevilla", "42": "Soria", "43": "Tarragona", "44": "Teruel",
    "45": "Toledo", "46": "Valencia/València", "47": "Valladolid",
    "48": "Bizkaia", "49": "Zamora", "50": "Zaragoza", "51": "Ceuta",
    "52": "Melilla",
}

PROVINCE_TO_COMMUNITY = {
    "04": "01", "11": "01", "14": "01", "18": "01", "21": "01", "23": "01", "29": "01", "41": "01",
    "22": "02", "44": "02", "50": "02", "33": "03", "07": "04", "35": "05", "38": "05", "39": "06",
    "05": "07", "09": "07", "24": "07", "34": "07", "37": "07", "40": "07", "42": "07", "47": "07", "49": "07",
    "02": "08", "13": "08", "16": "08", "19": "08", "45": "08", "08": "09", "17": "09", "25": "09", "43": "09",
    "03": "10", "12": "10", "46": "10", "06": "11", "10": "11", "15": "12", "27": "12", "32": "12", "36": "12",
    "28": "13", "30": "14", "31": "15", "01": "16", "20": "16", "48": "16", "26": "17", "51": "18", "52": "19",
}


def clean_code(value, size):
    if value is None:
        return ""
    digits = "".join(character for character in str(value) if character.isdigit())
    return digits.zfill(size)[-size:]


def main(source, destination):
    workbook = openpyxl.load_workbook(source, read_only=True, data_only=True)
    grouped = {code: [] for code in COMMUNITIES}
    count = 0

    for sheet in workbook.worksheets:
        headers = None
        for row in sheet.iter_rows(values_only=True):
            values = ["" if value is None else str(value).strip() for value in row]
            if "CPRO" in values and "NOMBRE" in values:
                headers = {name: index for index, name in enumerate(values)}
                continue
            if not headers:
                continue
            province_code = clean_code(values[headers["CPRO"]], 2)
            municipality_code = clean_code(values[headers["CMUN"]], 3)
            name = values[headers["NOMBRE"]].strip()
            community_code = PROVINCE_TO_COMMUNITY.get(province_code)
            if not community_code or not name:
                continue
            full_code = province_code + municipality_code
            grouped[community_code].append((province_code, {
                "id": full_code,
                "ine_code": full_code,
                "name": name,
                "postalCodes": [],
            }))
            count += 1

    catalog = []
    for community_code, community_name in COMMUNITIES.items():
        province_groups = {}
        for province_code, municipality in grouped[community_code]:
            province_groups.setdefault(province_code, []).append(municipality)
        provinces = []
        for province_code, municipalities in sorted(province_groups.items(), key=lambda item: PROVINCES[item[0]]):
            provinces.append({
                "id": province_code,
                "name": PROVINCES[province_code],
                "municipalities": sorted(municipalities, key=lambda item: item["name"]),
            })
        catalog.append({"id": community_code, "name": community_name, "provinces": provinces})

    Path(destination).write_text(
        json.dumps(catalog, ensure_ascii=False, indent=2) + "\n",
        encoding="utf-8",
    )
    print(json.dumps({"ccaa": len(catalog), "provinces": len(PROVINCES), "municipalities": count}, ensure_ascii=False))


if __name__ == "__main__":
    if len(sys.argv) != 3:
        raise SystemExit("Uso: build-territory-json.py 26codmun.xlsx territorios-espana.json")
    main(sys.argv[1], sys.argv[2])
