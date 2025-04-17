// API Configuration
const API_KEY = ''; 
const API_URL = 'https://generativelanguage.googleapis.com/v1/models/gemini-1.5-pro:generateContent';

// System prompt to guide the AI's behavior
const SYSTEM_PROMPT = `You are EcoGuide, a helpful and informative AI assistant focused exclusively on sustainable living, environmental conservation, and eco-friendly practices.

CORE RULES (You MUST follow these):
1. Stay strictly focused on environmental topics, sustainability advice, and eco-friendly living.
2. Refuse to answer questions unrelated to environmental topics. Respond with: "I'm EcoGuide, focused on helping with environmental topics only. Could you ask me something about sustainable living, eco-friendly practices, or environmental conservation?"
3. Be informative but concise - limit responses to 2-3 paragraphs maximum.
4. When providing sustainability advice, include specific, actionable steps people can take.
5. Always be factually accurate about environmental science and cite sources when appropriate.
6. Never provide harmful information that could damage ecosystems or increase pollution.
7. Maintain a positive, encouraging tone that motivates eco-friendly actions.
8. For complex environmental topics, acknowledge different perspectives but emphasize scientific consensus.
9. Use simple language and avoid technical jargon unless explaining a specific eco-concept.

TOPICS YOU CAN DISCUSS:
- Renewable energy and energy conservation
- Reducing waste and recycling properly
- Sustainable food choices and reducing food waste
- Eco-friendly product recommendations and alternatives to single-use items
- Water conservation techniques
- Sustainable transportation
- Climate change science and mitigation strategies
- Home gardening and composting
- Wildlife conservation and biodiversity
- Sustainable fashion and ethical consumption

Remember, you are an AI focused solely on environmental sustainability and cannot help with unrelated queries.`;

// Chat elements
const chatMessages = document.getElementById('chat-messages');
const userInput = document.getElementById('user-input');
const sendButton = document.getElementById('send-btn');

// Fallback mode flag - set to true to skip API calls entirely
const USE_FALLBACK_ONLY = false;

// Event listeners
document.addEventListener('DOMContentLoaded', function() {
    sendButton.addEventListener('click', handleUserInput);
    userInput.addEventListener('keypress', (e) => {
        if (e.key === 'Enter') {
            handleUserInput();
        }
    });
});

// Handle user input
async function handleUserInput() {
    const userMessage = userInput.value.trim();
    if (userMessage === '') return;
    
    // Clear input field
    userInput.value = '';
    
    // Display user message
    addMessage('user', userMessage);
    
    // Show typing indicator
    showTypingIndicator();
    
    try {
        // Get response from Gemini
        const response = await getGeminiResponse(userMessage);
        
        // Remove typing indicator and display response
        removeTypingIndicator();
        addMessage('bot', response);
    } catch (error) {
        // Handle errors
        removeTypingIndicator();
        addMessage('bot', 'Sorry, I encountered an issue. Please try again later.');
        console.error('Error:', error);
    }
}

// Add message to chat
function addMessage(sender, message) {
    const messageDiv = document.createElement('div');
    messageDiv.classList.add('message', sender);
    
    let avatar = '';
    if (sender === 'bot') {
        avatar = '<div class="avatar"><i class="fas fa-robot"></i></div>';
    } else {
        avatar = '<div class="avatar"><i class="fas fa-user"></i></div>';
    }
    
    messageDiv.innerHTML = `
        ${avatar}
        <div class="message-content">
            <div class="message-text">
                <p>${formatMessage(message)}</p>
            </div>
        </div>
    `;
    
    chatMessages.appendChild(messageDiv);
    
    // Scroll to bottom
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Format message with basic markdown-like processing
function formatMessage(text) {
    // Replace line breaks with paragraph tags
    text = text.replace(/\n\n/g, '</p><p>');
    
    // Simple formatting
    text = text.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');
    text = text.replace(/\*(.*?)\*/g, '<em>$1</em>');
    
    // Ensure we start and end with paragraph tags
    if (!text.startsWith('<p>')) text = '<p>' + text;
    if (!text.endsWith('</p>')) text += '</p>';
    
    return text;
}

// Show typing indicator
function showTypingIndicator() {
    const typingDiv = document.createElement('div');
    typingDiv.classList.add('message', 'bot', 'typing-indicator-container');
    typingDiv.id = 'typing-indicator';
    
    typingDiv.innerHTML = `
        <div class="avatar">
            <i class="fas fa-robot"></i>
        </div>
        <div class="message-content">
            <div class="typing-indicator">
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
                <div class="typing-dot"></div>
            </div>
        </div>
    `;
    
    chatMessages.appendChild(typingDiv);
    chatMessages.scrollTop = chatMessages.scrollHeight;
}

// Remove typing indicator
function removeTypingIndicator() {
    const typingIndicator = document.getElementById('typing-indicator');
    if (typingIndicator) {
        typingIndicator.remove();
    }
}

// Get response from Gemini API
async function getGeminiResponse(userMessage) {
    try {
        // Create the request body
        const requestBody = {
            contents: [
                {
                    parts: [
                        { text: SYSTEM_PROMPT + "\n\nUser query: " + userMessage }
                    ]
                }
            ],
            generationConfig: {
                temperature: 0.7,
                maxOutputTokens: 800
            }
        };
        
        // Make the API request
        const response = await fetch(`${API_URL}?key=${API_KEY}`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify(requestBody)
        });
        
        // Parse the response
        const data = await response.json();
        
        // Check for API errors
        if (!response.ok) {
            console.error("API Error:", data);
            if (data.error && data.error.message) {
                return `Error: ${data.error.message}`;
            }
            return "Sorry, I encountered an API error.";
        }
        
        // Extract and return the response text
        if (data.candidates && data.candidates[0] && data.candidates[0].content && data.candidates[0].content.parts && data.candidates[0].content.parts[0]) {
            return data.candidates[0].content.parts[0].text;
        } else {
            console.error("Unexpected API response format:", data);
            return "Sorry, I received an unexpected response format from the API.";
        }
    } catch (error) {
        console.error("Error in getGeminiResponse:", error);
        return "I encountered a technical issue. Please try again later.";
    }
}

// Fallback mechanism if API fails consistently
let apiFailCount = 0;
const MAX_API_FAILS = 3;

// Fallback response generator
function getFallbackResponse(userMessage) {
    const userMessageLower = userMessage.toLowerCase();
    
    if (userMessageLower.includes('hello') || userMessageLower.includes('hi')) {
        return "Hello there! I'm EcoGuide, your sustainable living assistant. How can I help you with environmental topics today?";
    }
    else if (userMessageLower.includes('recycl')) {
        return "Recycling is crucial for reducing waste! Here are some tips:\n\n1. Learn your local recycling rules - not all plastics are recyclable everywhere\n\n2. Clean and dry items before recycling them\n\n3. Consider composting food waste instead of sending it to landfill";
    }
    else if (userMessageLower.includes('energy') || userMessageLower.includes('electricity')) {
        return "To reduce your energy consumption:\n\n• Switch to LED bulbs which use 75% less energy than incandescent lighting\n\n• Unplug electronic devices when not in use or use smart power strips\n\n• Consider renewable energy options like solar panels for your home";
    }
    else if (userMessageLower.includes('water')) {
        return "Water conservation is essential! Try these practical steps:\n\n• Fix leaky faucets - they can waste up to 3,000 gallons per year\n\n• Install low-flow showerheads and toilets\n\n• Collect rainwater for garden use instead of using treated water";
    }
    else if (userMessageLower.includes('plastic') || userMessageLower.includes('single use')) {
        return "Reducing plastic usage is vital for our oceans. Consider these alternatives:\n\n• Carry reusable shopping bags, water bottles, and coffee cups\n\n• Choose products with minimal or plastic-free packaging\n\n• Use beeswax wraps instead of plastic cling film";
    }
    else if (userMessageLower.includes('garden') || userMessageLower.includes('plant')) {
        return "Sustainable gardening helps biodiversity and reduces your carbon footprint:\n\n• Choose native plants that support local wildlife\n\n• Create a compost bin for garden and kitchen waste\n\n• Collect rainwater in barrels for watering plants";
    }
    else {
        return "That's an important environmental topic. Sustainable living isn't just good for the planet - it often saves money and creates healthier living spaces. Start with small changes like reducing single-use items, conserving water and energy, and being mindful about consumption. Would you like more specific information about a particular aspect of eco-friendly living?";
    }
}