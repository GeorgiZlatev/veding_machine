async function request(action = 'state', data = {}) {
    const response = await fetch('api.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ action, data })
    });

    const result = await response.json();
    if (!result.ok) {
        alert(result.message);
        return;
    }

    render(result.state);
}

function render(state) {
    document.querySelector('#balance').textContent = state.balance;

    const drinks = document.querySelector('#drinks');
    drinks.replaceChildren();

    Object.entries(state.drinks).forEach(([id, drink]) => {
        const line = document.createElement('p');
        const button = document.createElement('button');

        button.textContent = `Купи ${drink.name} (${drink.priceLabel})`;
        button.disabled = drink.quantity < 1;
        button.onclick = () => request('drink.buy', { id });

        line.append(button, `Наличност: ${drink.quantity}`);
        drinks.append(line);
    });

    const coins = document.querySelector('#coin-buttons');
    coins.replaceChildren();

    state.coins.forEach(coin => {
        const button = document.createElement('button');
        button.textContent = `Добави ${(coin / 100).toFixed(2)} лв.`;
        button.onclick = () => request('coin.insert', { value: coin / 100 });

        coins.append(button);
    });

    const display = document.querySelector('#display');
    display.replaceChildren();

    state.messages.forEach(message => {
        const item = document.createElement('li');
        item.textContent = message;
        display.append(item);
    });

}

document.querySelector('#change-button').onclick = () => request('coin.change');
document.querySelector('#reset-button').onclick = () => request('reset');

request();