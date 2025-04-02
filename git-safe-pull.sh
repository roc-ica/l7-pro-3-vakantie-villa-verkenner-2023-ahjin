#!/bin/bash

# Colors for better output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[0;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
TARGET_BRANCH="development"
CURRENT_BRANCH=$(git rev-parse --abbrev-ref HEAD)

# Function to display changes ahead of current branch
show_changes_ahead() {
    local branch="$1"
    echo -e "\n${BLUE}Checking changes ahead of '$branch'...${NC}"
    
    # Get commit count ahead
    ahead_count=$(git rev-list --count origin/$branch..$branch 2>/dev/null)
    
    if [ -z "$ahead_count" ]; then
        echo -e "${YELLOW}No upstream branch found for '$branch'.${NC}"
        return
    fi
    
    if [ "$ahead_count" -eq 0 ]; then
        echo -e "${GREEN}Your branch is up to date with origin/$branch${NC}"
    else
        echo -e "${YELLOW}You have $ahead_count commit(s) ahead of origin/$branch:${NC}"
        echo -e "${BLUE}--------------------------------------------------${NC}"
        git log --pretty=format:"%C(yellow)%h %Creset%s %Cgreen(%cr) %C(bold blue)<%an>%Creset" origin/$branch..$branch
        echo -e "${BLUE}--------------------------------------------------${NC}"
    fi
}

# Main script execution
echo -e "\n${BLUE}=== Git Branch Sync Helper ===${NC}"
echo -e "Current branch: ${GREEN}$CURRENT_BRANCH${NC}"
echo -e "Target branch:  ${GREEN}$TARGET_BRANCH${NC}"

if [ "$CURRENT_BRANCH" != "$TARGET_BRANCH" ]; then
    echo -e "\n${RED}Warning:${NC} You're on '${RED}$CURRENT_BRANCH${NC}' but trying to pull '${GREEN}$TARGET_BRANCH${NC}'"
    echo -e "Switching to ${GREEN}$TARGET_BRANCH${NC} first..."
    git checkout $TARGET_BRANCH || exit 1
    CURRENT_BRANCH=$TARGET_BRANCH
fi

# Show changes before pulling
show_changes_ahead "$CURRENT_BRANCH"

echo -e "\n${BLUE}Pulling latest changes for '$CURRENT_BRANCH'...${NC}"
git pull origin $TARGET_BRANCH

# Show status after pulling
echo -e "\n${BLUE}=== Post-pull Status ===${NC}"
git status -sb