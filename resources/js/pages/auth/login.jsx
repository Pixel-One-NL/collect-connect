import { Link, useForm } from '@inertiajs/react';
import Container from '../../components/Container';
import Button from '../../components/UI/Button';

export default function Login() {
    const form = useForm({ email: '', password: '', remember: false });

    return (
        <Container className="max-w-md my-12">
            <h1 className="text-2xl font-bold mb-6">Inloggen</h1>
            <form
                className="space-y-3"
                onSubmit={(e) => {
                    e.preventDefault();
                    form.post('/login');
                }}
            >
                <input className="w-full border rounded-md px-3 py-2" type="email" placeholder="E-mail" value={form.data.email} onChange={(e) => form.setData('email', e.target.value)} required />
                <input className="w-full border rounded-md px-3 py-2" type="password" placeholder="Wachtwoord" value={form.data.password} onChange={(e) => form.setData('password', e.target.value)} required />
                <Button type="submit" variant="primary" className="w-full justify-center" disabled={form.processing}>Inloggen</Button>
                {form.errors.email && <p className="text-sm text-red-600">{form.errors.email}</p>}
            </form>
            <p className="mt-4 text-sm text-gray-600">
                Nog geen account? <Link href="/register" className="underline">Registreren</Link>
            </p>
        </Container>
    );
}
