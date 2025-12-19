#!/bin/bash

echo "🚀 Setting up SEO-Friendly Tutor Search System..."
echo ""

# Colors
GREEN='\033[0;32m'
BLUE='\033[0;34m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Step 1: Run migrations
echo -e "${BLUE}📦 Running database migrations...${NC}"
php artisan migrate --force
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Migrations completed${NC}"
else
    echo -e "${YELLOW}⚠️  Migration failed or already run${NC}"
fi
echo ""

# Step 2: Configure Meilisearch
echo -e "${BLUE}🔍 Configuring Meilisearch...${NC}"
php artisan meilisearch:configure
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Meilisearch configured${NC}"
else
    echo -e "${YELLOW}⚠️  Meilisearch configuration failed${NC}"
fi
echo ""

# Step 3: Import tutors to Meilisearch
echo -e "${BLUE}📊 Importing tutors to Meilisearch...${NC}"
php artisan scout:import "App\Models\Tutor"
if [ $? -eq 0 ]; then
    echo -e "${GREEN}✅ Tutors imported successfully${NC}"
else
    echo -e "${YELLOW}⚠️  Import failed (check if Meilisearch is running)${NC}"
fi
echo ""

# Step 4: Clear cache
echo -e "${BLUE}🧹 Clearing application cache...${NC}"
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear
echo -e "${GREEN}✅ Cache cleared${NC}"
echo ""

# Step 5: Optimize for production (optional)
read -p "Optimize for production? (y/n) " -n 1 -r
echo
if [[ $REPLY =~ ^[Yy]$ ]]; then
    echo -e "${BLUE}⚡ Optimizing application...${NC}"
    php artisan config:cache
    php artisan route:cache
    php artisan view:cache
    echo -e "${GREEN}✅ Application optimized${NC}"
fi
echo ""

# Summary
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${GREEN}✨ Setup Complete!${NC}"
echo -e "${GREEN}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo ""
echo -e "${BLUE}📋 Next Steps:${NC}"
echo "1. Ensure Meilisearch is running on port 7700"
echo "2. Ensure Redis is running on port 6379"
echo "3. Test the search at: /tutors"
echo "4. Try SEO URLs: /mathematics-tutors-in-delhi"
echo ""
echo -e "${BLUE}📚 Documentation:${NC}"
echo "Read SEARCH_SYSTEM_README.md for complete guide"
echo ""
echo -e "${BLUE}🧪 Test API:${NC}"
echo "curl http://localhost/api/tutors?subject=mathematics"
echo ""
echo -e "${GREEN}Happy Coding! 🚀${NC}"
