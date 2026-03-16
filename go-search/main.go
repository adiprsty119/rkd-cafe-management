package main

import (
	"database/sql"
	"encoding/json"
	"log"
	"net/http"

	_ "github.com/go-sql-driver/mysql"
)

type User struct {
	ID       int    `json:"id"`
	Name     string `json:"name"`
	Username string `json:"username"`
	Email    string `json:"email"`
	Role     string `json:"role"`
}

type Menu struct {
	ID    int     `json:"id"`
	Name  string  `json:"name"`
	Price float64 `json:"price"`
}

type Order struct {
	ID        int     `json:"id"`
	OrderCode string  `json:"order_code"`
	Total     float64 `json:"total"`
}

type SearchResult struct {
	Users  []User  `json:"users"`
	Menu   []Menu  `json:"menu"`
	Orders []Order `json:"orders"`
}

var db *sql.DB

func searchHandler(w http.ResponseWriter, r *http.Request) {

	keyword := r.URL.Query().Get("keyword")
	keyword = "%" + keyword + "%"

	result := SearchResult{
		Users:  []User{},
		Menu:   []Menu{},
		Orders: []Order{},
	}

	/* USERS */

	rowsUsers, err := db.Query(`
		SELECT id,name,username,email,role
		FROM users
		WHERE
			name LIKE ?
			OR username LIKE ?
			OR email LIKE ?
		LIMIT 5
	`, keyword, keyword, keyword)

	if err == nil {

		defer rowsUsers.Close()

		for rowsUsers.Next() {

			var u User

			err := rowsUsers.Scan(&u.ID, &u.Name, &u.Username, &u.Email, &u.Role)
			if err != nil {
				log.Println(err)
				continue
			}

			result.Users = append(result.Users, u)

		}

	}

	/* PRODUCTS */

	rowsProducts, err := db.Query(`
		SELECT id,name,price
		FROM products
		WHERE name LIKE ?
		LIMIT 5
	`, keyword)

	if err == nil {

		defer rowsProducts.Close()

		for rowsProducts.Next() {

			var m Menu

			rowsProducts.Scan(&m.ID, &m.Name, &m.Price)

			result.Menu = append(result.Menu, m)

		}

	}

	/* ORDERS */

	rowsOrders, err := db.Query(`
		SELECT id,invoice_code,total
		FROM orders
		WHERE invoice_code LIKE ?
		LIMIT 5
	`, keyword)

	if err == nil {

		defer rowsOrders.Close()

		for rowsOrders.Next() {

			var o Order

			rowsOrders.Scan(&o.ID, &o.OrderCode, &o.Total)

			result.Orders = append(result.Orders, o)

		}

	}

	w.Header().Set("Content-Type", "application/json")

	json.NewEncoder(w).Encode(result)

}

func main() {

	var err error

	db, err = sql.Open(
		"mysql",
		"root:@tcp(127.0.0.1:3306)/rkd_cafe",
	)

	if err != nil {
		log.Fatal(err)
	}

	/* ROOT TEST ROUTE */
	http.HandleFunc("/", func(w http.ResponseWriter, r *http.Request) {
		w.Write([]byte("Go Search API Running"))
	})

	/* SEARCH ROUTE */
	http.HandleFunc("/search", searchHandler)

	log.Println("Go Search Service running at :8082")

	http.ListenAndServe(":8082", nil)

}
