const searchInput = document.getElementById("searchInput");
const resultsBox = document.getElementById("searchResults");

let debounceTimer;
let currentFocus = -1;
let controller = null;

/* ==========================
   INPUT LISTENER
========================== */

searchInput.addEventListener("keyup", function () {

    const keyword = this.value.trim();

    clearTimeout(debounceTimer);

    if (keyword.length < 2) {
        resultsBox.classList.add("hidden");
        return;
    }

    debounceTimer = setTimeout(() => {
        globalSearch(keyword);
    }, 250);

});

/* ==========================
   KEYBOARD NAVIGATION
========================== */

searchInput.addEventListener("keydown", function(e){

    const items = resultsBox.querySelectorAll(".search-item");

    if(!items.length) return;

    if(e.key === "ArrowDown"){
        currentFocus++;
        setActive(items);
        e.preventDefault();
    }

    if(e.key === "ArrowUp"){
        currentFocus--;
        setActive(items);
        e.preventDefault();
    }

    if(e.key === "Enter"){
        e.preventDefault();
        if(currentFocus > -1 && items[currentFocus]){
            items[currentFocus].click();
        }
    }

    if(e.key === "Escape"){
        resultsBox.classList.add("hidden");
    }

});

function setActive(items){

    items.forEach(el =>
        el.classList.remove("bg-gray-100","dark:bg-gray-700")
    );

    if(currentFocus >= items.length) currentFocus = 0;
    if(currentFocus < 0) currentFocus = items.length - 1;

    items[currentFocus].classList.add(
        "bg-gray-100","dark:bg-gray-700"
    );
}

/* ==========================
   GRAPHQL SEARCH
========================== */

async function globalSearch(keyword)
{

    if(controller){
        controller.abort();
    }

    controller = new AbortController();

    resultsBox.innerHTML = `
        <div class="p-3 text-sm text-gray-400">
            Searching...
        </div>
    `;

    resultsBox.classList.remove("hidden");

    try {

        const response = await fetch("/rkd-cafe/graphql/server.php", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            signal: controller.signal,

            body: JSON.stringify({
                query: `
                    query {
                        search(keyword:"${keyword}") {

                            users {
                                id
                                name
                                username
                                role
                            }

                            menu {
                                id
                                name
                                price
                            }

                            orders {
                                id
                                order_code
                                total
                            }

                        }
                    }
                `
            })

        });

        const data = await response.json();

        if (!data || !data.data || !data.data.search) {
            console.error("Invalid GraphQL response:", data);
            return;
        }

        renderResults(data.data.search, keyword);

    } catch (error) {

        if(error.name !== "AbortError"){
            console.error("Search error:", error);
        }

    }
}

/* ==========================
   HIGHLIGHT KEYWORD
========================== */

function highlight(text, keyword){

    const regex = new RegExp(`(${keyword})`, "gi");

    return text.replace(
        regex,
        `<mark class="bg-yellow-200 text-black">$1</mark>`
    );

}

/* ==========================
   ESCAPE HTML
========================== */

function escapeHTML(str){

    return str
        .replace(/&/g,"&amp;")
        .replace(/</g,"&lt;")
        .replace(/>/g,"&gt;")
        .replace(/"/g,"&quot;")
        .replace(/'/g,"&#039;");

}

/* ==========================
   RENDER RESULTS
========================== */

function renderResults(data, keyword)
{

    resultsBox.innerHTML = "";
    currentFocus = -1;

    let hasResult = false;

    /* USERS */

    if (data.users && data.users.length) {

        resultsBox.innerHTML += `
        <div class="px-4 py-1 text-xs text-gray-400 uppercase">
            Users
        </div>
        `;

        data.users.forEach(user => {

            hasResult = true;

            const item = document.createElement("div");

            item.className =
                "search-item px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer";

            const name = highlight(
                escapeHTML(user.name),
                keyword
            );

            const username = highlight(
                escapeHTML(user.username ?? ""),
                keyword
            );

            item.innerHTML = `
                <div class="flex flex-col">
                    <span class="font-medium text-sm">${name}</span>
                    <span class="text-xs text-gray-400">${username}</span>
                    <span class="text-[10px] text-yellow-500 uppercase">
                        ${user.role ?? "user"}
                    </span>
                </div>
            `;

            item.onclick = () => {
                window.location =
                    "/rkd-cafe/public/profile.php?id=" + user.id;
            };

            resultsBox.appendChild(item);

        });

    }

    /* MENU */

    if (data.menu && data.menu.length) {

        resultsBox.innerHTML += `
        <div class="px-4 py-1 text-xs text-gray-400 uppercase">
            Menu
        </div>
        `;

        data.menu.forEach(menu => {

            hasResult = true;

            const item = document.createElement("div");

            item.className =
                "search-item px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer";

            const name = highlight(
                escapeHTML(menu.name),
                keyword
            );

            item.innerHTML = `
                <div class="flex flex-col">
                    <span class="font-medium text-sm">${name}</span>
                    <span class="text-xs text-green-500">
                        Rp ${menu.price}
                    </span>
                </div>
            `;

            resultsBox.appendChild(item);

        });

    }

    /* ORDERS */

    if (data.orders && data.orders.length) {

        resultsBox.innerHTML += `
        <div class="px-4 py-1 text-xs text-gray-400 uppercase">
            Orders
        </div>
        `;

        data.orders.forEach(order => {

            hasResult = true;

            const item = document.createElement("div");

            item.className =
                "search-item px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer";

            item.innerHTML = `
                <div class="flex flex-col">
                    <span class="font-medium text-sm">
                        ${order.order_code}
                    </span>
                    <span class="text-xs text-yellow-500">
                        Rp ${order.total}
                    </span>
                </div>
            `;

            resultsBox.appendChild(item);

        });

    }

    /* NO RESULT */

    if (!hasResult) {

        resultsBox.innerHTML = `
        <div class="p-3 text-sm text-gray-500">
            No results found
        </div>
        `;

    }

    resultsBox.classList.remove("hidden");

}

/* ==========================
   CLICK OUTSIDE CLOSE
========================== */

document.addEventListener("click", function(e){

    if(
        !searchInput.contains(e.target) &&
        !resultsBox.contains(e.target)
    ){
        resultsBox.classList.add("hidden");
    }

});

/* ==========================
   COMMAND PALETTE (CTRL+K)
========================== */

document.addEventListener("keydown", function(e){

    if((e.ctrlKey || e.metaKey) && e.key === "k"){

        e.preventDefault();

        searchInput.focus();
        searchInput.select();

    }

});