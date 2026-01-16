// MongoDB Seed Script for Progressive Bar
// Run with: node scripts/seed-database.js

const { MongoClient, ObjectId } = require("mongodb");
require("dotenv").config({ path: ".env" });

const MONGODB_URI = process.env.MONGODB_URI || "mongodb://localhost:27017";
const DATABASE_NAME = process.env.MONGODB_DATABASE || "progressive_bar";

const menuItems = [
  // Signature Cocktails
  {
    _id: new ObjectId(),
    name: "Progressive Sunset",
    description: "Tequila, blood orange, grapefruit, agave, chili rim",
    price: 16,
    category: "cocktails",
    image: "/images/sunset.jpg",
    available: true,
    preparationTime: 5,
    tags: ["signature", "spicy", "tequila"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Midnight Reverie",
    description: "Vodka, blue curaçao, butterfly pea flower, lime, elderflower",
    price: 15,
    category: "cocktails",
    image: "/images/midnight.jpg",
    available: true,
    preparationTime: 5,
    tags: ["signature", "vodka", "floral"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Smoked Old Fashioned",
    description: "Bourbon, maple, aromatic bitters, orange peel, hickory smoke",
    price: 18,
    category: "cocktails",
    image: "/images/old-fashioned.jpg",
    available: true,
    preparationTime: 6,
    tags: ["premium", "whiskey", "smoky"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Velvet Martini",
    description: "Grey Goose, Lillet Blanc, lavender, lemon twist",
    price: 17,
    category: "cocktails",
    image: "/images/martini.jpg",
    available: true,
    preparationTime: 4,
    tags: ["classic", "vodka", "elegant"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Garden Mojito",
    description: "Bacardi, fresh mint, cucumber, lime, soda",
    price: 14,
    category: "cocktails",
    image: "/images/mojito.jpg",
    available: true,
    preparationTime: 4,
    tags: ["refreshing", "rum", "light"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Espresso Martini",
    description: "Absolut, Kahlúa, fresh espresso, vanilla",
    price: 16,
    category: "cocktails",
    image: "/images/espresso-martini.jpg",
    available: true,
    preparationTime: 5,
    tags: ["popular", "coffee", "vodka"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },

  // Premium Drinks
  {
    _id: new ObjectId(),
    name: "Macallan 12yr",
    description: "Single malt scotch, neat or on the rocks",
    price: 22,
    category: "premium",
    image: "/images/macallan.jpg",
    available: true,
    preparationTime: 1,
    tags: ["whiskey", "scotch", "neat"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Clase Azul Reposado",
    description: "Premium aged tequila, served neat",
    price: 32,
    category: "premium",
    image: "/images/clase-azul.jpg",
    available: true,
    preparationTime: 1,
    tags: ["tequila", "premium", "neat"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },

  // Wines
  {
    _id: new ObjectId(),
    name: "Caymus Cabernet",
    description: "Napa Valley, full-bodied red",
    price: 28,
    category: "wine",
    image: "/images/caymus.jpg",
    available: true,
    preparationTime: 2,
    tags: ["red", "california", "bold"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Whispering Angel Rosé",
    description: "Provence, France - crisp and refreshing",
    price: 16,
    category: "wine",
    image: "/images/rose.jpg",
    available: true,
    preparationTime: 2,
    tags: ["rosé", "french", "light"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },

  // Beer & Cider
  {
    _id: new ObjectId(),
    name: "Craft IPA Flight",
    description: "Four local IPA samples",
    price: 14,
    category: "beer",
    image: "/images/ipa-flight.jpg",
    available: true,
    preparationTime: 3,
    tags: ["craft", "local", "flight"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Japanese Lager",
    description: "Asahi Super Dry, ice cold",
    price: 8,
    category: "beer",
    image: "/images/asahi.jpg",
    available: true,
    preparationTime: 1,
    tags: ["lager", "imported", "crisp"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },

  // Snacks
  {
    _id: new ObjectId(),
    name: "Truffle Fries",
    description: "Hand-cut fries, truffle oil, parmesan, herbs",
    price: 12,
    category: "snacks",
    image: "/images/truffle-fries.jpg",
    available: true,
    preparationTime: 8,
    tags: ["vegetarian", "shareable"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Wagyu Sliders",
    description: "Three mini wagyu burgers, caramelized onion, special sauce",
    price: 22,
    category: "snacks",
    image: "/images/sliders.jpg",
    available: true,
    preparationTime: 12,
    tags: ["premium", "beef", "popular"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Charcuterie Board",
    description: "Cured meats, artisan cheeses, olives, bread",
    price: 28,
    category: "snacks",
    image: "/images/charcuterie.jpg",
    available: true,
    preparationTime: 5,
    tags: ["shareable", "premium"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Crispy Calamari",
    description: "Lightly fried, lemon aioli, marinara",
    price: 16,
    category: "snacks",
    image: "/images/calamari.jpg",
    available: true,
    preparationTime: 10,
    tags: ["seafood", "shareable"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },

  // Non-Alcoholic
  {
    _id: new ObjectId(),
    name: "Virgin Mojito",
    description: "Fresh mint, lime, soda, no alcohol",
    price: 8,
    category: "mocktails",
    image: "/images/virgin-mojito.jpg",
    available: true,
    preparationTime: 3,
    tags: ["non-alcoholic", "refreshing"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
  {
    _id: new ObjectId(),
    name: "Sunset Spritz",
    description: "Orange, grapefruit, sparkling water, no alcohol",
    price: 9,
    category: "mocktails",
    image: "/images/sunset-spritz.jpg",
    available: true,
    preparationTime: 3,
    tags: ["non-alcoholic", "citrus"],
    createdAt: new Date(),
    updatedAt: new Date(),
  },
];

const adminUser = {
  _id: new ObjectId(),
  email: "admin@progressivebar.com",
  // Password: 'admin123' - hashed with password_hash in PHP
  passwordHash: "$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi",
  name: "Bar Admin",
  role: "admin",
  createdAt: new Date(),
  updatedAt: new Date(),
};

async function seed() {
  const client = new MongoClient(MONGODB_URI);

  try {
    await client.connect();
    console.log("Connected to MongoDB");

    const db = client.db(DATABASE_NAME);

    // Drop existing collections
    const collections = await db.listCollections().toArray();
    for (const coll of collections) {
      if (["menu_items", "admins", "orders"].includes(coll.name)) {
        await db.collection(coll.name).drop();
        console.log(`Dropped collection: ${coll.name}`);
      }
    }

    // Insert menu items
    const menuResult = await db.collection("menu_items").insertMany(menuItems);
    console.log(`Inserted ${menuResult.insertedCount} menu items`);

    // Insert admin user
    await db.collection("admins").insertOne(adminUser);
    console.log("Inserted admin user (admin@progressivebar.com / admin123)");

    // Create indexes
    await db.collection("orders").createIndex({ tableNumber: 1 });
    await db.collection("orders").createIndex({ status: 1 });
    await db.collection("orders").createIndex({ createdAt: -1 });
    await db.collection("menu_items").createIndex({ category: 1 });
    await db.collection("menu_items").createIndex({ available: 1 });
    await db.collection("admins").createIndex({ email: 1 }, { unique: true });
    console.log("Created database indexes");

    console.log("\n✅ Database seeded successfully!");
    console.log("\nAdmin Login:");
    console.log("  Email: admin@progressivebar.com");
    console.log("  Password: admin123");
  } catch (error) {
    console.error("Seed failed:", error);
    process.exit(1);
  } finally {
    await client.close();
  }
}

seed();
