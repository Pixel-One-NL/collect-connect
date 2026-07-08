import Container from "../Container.jsx";
import {MapPinIcon} from "@phosphor-icons/react/dist/csr/MapPin";
import {PhoneIcon} from "@phosphor-icons/react/dist/csr/Phone";
import {TruckIcon} from "@phosphor-icons/react/dist/csr/Truck";

export default function HeaderTopBar() {
    return (
        <div className="bg-primary text-white py-0.5 sm:py-2 text-sm">
            <Container className="flex gap-4 items-center justify-center lg:justify-between">
                <div className="hidden sm:block">
                    <p>Dé plek voor al jouw lego onderdelen en minifiguren</p>
                </div>

                <div className="gap-2 items-center hidden lg:flex">
                    <a href="#" className="inline-flex items-center gap-2 hover:underline">
                        <PhoneIcon />
                        <p>+31 123 456 78</p>
                    </a>

                    <a href="#" className="inline-flex items-center gap-2 hover:underline">
                        <MapPinIcon />
                        <p>+31 123 456 78</p>
                    </a>

                    <a href="#" className="inline-flex items-center gap-2 hover:underline">
                        <TruckIcon />
                        <p>+31 123 456 78</p>
                    </a>
                </div>
            </Container>
        </div>
    );
}
