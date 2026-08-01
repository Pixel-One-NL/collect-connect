import { Link, useForm } from '@inertiajs/react';
import Container from '../../components/Container';
import Button from '../../components/UI/Button';

export default function Register() {
    const form = useForm({ name: '', email: '', password: '', password_confirmation: '' });

    return (
        <Container className="max-w-md my-12">
            <h1 className="text-2xl font-bold mb-6">Account aanmaken</h1>
            <form
                className="space-y-3"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/register');
                }}
            >
                <input className="w-full border rounded-md px-3 py-2" placeholder="Naam" value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                <input className="w-full border rounded-md px-3 py-2" type="email" placeholder="E-mail" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required />
                <input className="w-full border rounded-md px-3 py-2" type="password" placeholder="Wachtwoord" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} required />
                <input className="w-full border rounded-md px-3 py-2" type="password" placeholder="Bevestig wachtwoord" value={form.data.password_confirmation} onChange={(e) => form.setData('password_confirmation', e.target.value)} required />
                <Button type="submit" variant="primary" className="w-full justify-center" disabled={form.processing}>Registreren</Button>
            </form>
            <p className="mt-4 text-sm text-gray-600">
                Al een account? <Link href="/login" className="underline">Inloggen</Link>
            </p>
        </Container>
    );
}
