const searchInput = document.getElementById("searchInput");
const resultsBox = document.getElementById("searchResults");

let debounceTimer;

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

    }, 300);

});

/* ==========================
   GRAPHQL SEARCH
========================== */

async function globalSearch(keyword)
{
    try {

        const response = await fetch("/rkd-cafe/graphql/server.php", {

            method: "POST",

            headers: {
                "Content-Type": "application/json"
            },

            body: JSON.stringify({
                query: `
                    query {
                        search(keyword:"${keyword}") {
                            users {
                                name
                                email
                                role
                            }
                        }
                    }
                `
            })

        });

        const data = await response.json();

        console.log("GraphQL response FULL:", JSON.stringify(data, null, 2));

        /* ==========================
           VALIDATION
        ========================== */

        if (!data || !data.data || !data.data.search) {

            console.error("Invalid GraphQL response:", data);
            return;

        }

        renderResults(data.data.search.users);

    } catch (error) {

        console.error("Search error:", error);

    }
}

/* ==========================
   RENDER RESULTS
========================== */

function renderResults(users)
{
    resultsBox.innerHTML = "";

    if (!users || users.length === 0) {

        resultsBox.innerHTML =
            `<div class="p-3 text-sm text-gray-500">No results found</div>`;

        resultsBox.classList.remove("hidden");

        return;

    }

    users.forEach(user => {

        const item = document.createElement("div");

        item.className =
            "px-4 py-2 hover:bg-gray-100 dark:hover:bg-gray-700 cursor-pointer";

        item.innerHTML = `
            <div class="font-medium text-sm">${user.name}</div>
            <div class="text-xs text-gray-500">${user.email}</div>
        `;

        resultsBox.appendChild(item);

    });

    resultsBox.classList.remove("hidden");
}