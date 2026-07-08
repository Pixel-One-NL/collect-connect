import {ListMagnifyingGlassIcon} from "@phosphor-icons/react/dist/csr/ListMagnifyingGlass";

export default function Home() {
    return (
        <>
            <div className="px-8 py-16 max-w-7xl mx-auto">
                <h1 className="text-4xl font-bold mb-4 text-[#333]">Welkom bij Collect2Connect</h1>
                <p className="text-lg text-gray-600">
                    Dé plek voor al jouw LEGO® onderdelen en minifiguren
                </p>
            </div>

            <section className="my-20 max-w-7xl mx-auto px-8">
                {/*<CollectionSlider />*/}
            </section>

            <section className="my-20 border-brick-y-secondary bg-secondary py-20 text-center px-8">
                <img src="/images/elements/rocket.png" alt="" className="absolute top-1/2 -left-10 -translate-y-1/2 rotate-30 hidden lg:block pointer-events-none z-10 max-h-72 lg:max-h-96" />

                <button className="px-8 py-4 bg-white rounded-lg w-full max-w-4xl inline-flex items-center gap-4 cursor-pointer">
                    <ListMagnifyingGlassIcon size={28} />
                    <span>Zoek op naam, onderdelen, minifiguren, lego-nummer...</span>
                </button>
            </section>
        </>
    );
}
