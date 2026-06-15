// menu.js
// Nuevo sistema: solo pestañas de categorías y productos de ejemplo

const categorias = [
  {
    nombre: "Tapas",
    productos: [
      { nombre: "Tortilla de patatas", descripcion: "Clásica tortilla española", precio: "3.50€" },
      { nombre: "Croquetas", descripcion: "De jamón ibérico", precio: "4.00€" }
    ]
  },
  {
    nombre: "Cafés",
    productos: [
      { nombre: "Café solo", descripcion: "Café espresso", precio: "1.20€" },
      { nombre: "Café con leche", descripcion: "Espresso con leche", precio: "1.50€" }
    ]
  },
  {
    nombre: "Refrescos",
    productos: [
      { nombre: "Coca-Cola", descripcion: "Botella 33cl", precio: "2.00€" },
      { nombre: "Fanta Naranja", descripcion: "Botella 33cl", precio: "2.00€" }
    ]
  },
  {
    nombre: "Bebidas alcohólicas",
    productos: [
      { nombre: "Cerveza", descripcion: "Caña nacional", precio: "1.80€" },
      { nombre: "Vino tinto", descripcion: "Copa Rioja", precio: "2.50€" }
    ]
  },
  {
    nombre: "Postres",
    productos: [
      { nombre: "Tarta de queso", descripcion: "Con mermelada de arándanos", precio: "3.00€" },
      { nombre: "Helado", descripcion: "Varios sabores", precio: "2.50€" }
    ]
  },
  {
    nombre: "Raciones",
    productos: [
      { nombre: "Calamares", descripcion: "A la romana", precio: "7.00€" },
      { nombre: "Patatas bravas", descripcion: "Con salsa picante", precio: "5.50€" }
    ]
  }
];

const categoriasDiv = document.getElementById("categorias");
const productosDiv = document.getElementById("productos");

document.addEventListener("DOMContentLoaded", () => {
  mostrarCategorias();
  mostrarProductos(0); // Mostrar la primera categoría por defecto
});

function mostrarCategorias() {
  categoriasDiv.innerHTML = "";
  categorias.forEach((cat, idx) => {
    const btn = document.createElement("button");
    btn.textContent = cat.nombre;
    btn.className =
      "px-4 py-2 rounded-xl font-bold text-[#256353] bg-[#E0FAF4] hover:bg-[#72E8AC]/30 border-b-4 border-transparent hover:border-[#72E8AC] transition-all duration-200";
    btn.addEventListener("click", () => mostrarProductos(idx));
    categoriasDiv.appendChild(btn);
  });
}

function mostrarProductos(idx) {
  // Quitar selección previa
  Array.from(categoriasDiv.children).forEach((btn, i) => {
    btn.classList.toggle("border-[#72E8AC]", i === idx);
    btn.classList.toggle("bg-[#72E8AC]/20", i === idx);
  });
  const cat = categorias[idx];
  productosDiv.innerHTML = "";
  if (!cat.productos.length) {
    productosDiv.innerHTML = '<div class="col-span-full text-center text-[#256353]">No hay productos en esta categoría.</div>';
    return;
  }
  cat.productos.forEach(prod => {
    const card = document.createElement("div");
    card.className = "bg-white/80 rounded-2xl shadow-lg p-5 flex flex-col gap-2 items-start border border-[#72E8AC]/30";
    card.innerHTML = `
      <div class="text-xl font-bold text-[#256353]">${prod.nombre}</div>
      <div class="text-[#21476B]">${prod.descripcion}</div>
      <div class="text-[#51B2E0] font-semibold">${prod.precio}</div>
    `;
    productosDiv.appendChild(card);
  });
}
